<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query;

use Closure;
use DateTimeInterface;
use Generator;
use mako\database\connections\Connection;
use mako\database\exceptions\NotFoundException;
use mako\database\query\compilers\Compiler;
use mako\database\query\helpers\HelperInterface;
use mako\pagination\PaginationFactoryInterface;
use PDO;

use function array_diff_key;
use function array_flip;
use function count;
use function get_class_vars;
use function is_array;
use function sprintf;

/**
 * Query builder.
 */
class Query
{
	/**
	 * Query helper.
	 */
	protected HelperInterface $helper;

	/**
	 * Query compiler.
	 */
	protected Compiler $compiler;

	/**
	 * Database table.
	 */
	protected null|array|Raw|string|Subquery $table = null;

	/**
	 * Select distinct?
	 */
	protected bool $distinct = false;

	/**
	 * Common table expressions.
	 */
	protected array $commonTableExpressions = ['recursive' => false, 'ctes' => []];

	/**
	 * Set operations.
	 */
	protected array $setOperations = [];

	/**
	 * Columns from which we are fetching data.
	 */
	protected array $columns = ['*'];

	/**
	 * WHERE clauses.
	 */
	protected array $wheres = [];

	/**
	 * JOIN clauses.
	 */
	protected array $joins = [];

	/**
	 * GROUP BY clauses.
	 */
	protected array $groupings = [];

	/**
	 * HAVING clauses.
	 */
	protected array $havings = [];

	/**
	 * ORDER BY clauses.
	 */
	protected array $orderings = [];

	/**
	 * Limit.
	 */
	protected ?int $limit = null;

	/**
	 * Offset.
	 */
	protected ?int $offset = null;

	/**
	 * Lock.
	 */
	protected null|bool|string $lock = null;

	/**
	 * Prefix.
	 */
	protected ?string $prefix = null;

	/**
	 * Is the query in subquery context?
	 */
	protected bool $inSubqueryContext = false;

	/**
	 * Pagination factory.
	 */
	protected static Closure|PaginationFactoryInterface $paginationFactory;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Connection $connection
	) {
		$this->helper = $connection->getQueryBuilderHelper();

		$this->compiler = $connection->getQueryCompiler($this);
	}

	/**
	 * Create a fresh compiler instance and clone set operation queries when we clone the query.
	 */
	public function __clone()
	{
		$this->compiler = $this->connection->getQueryCompiler($this);

		foreach ($this->setOperations as $key => $setOperation) {
			$this->setOperations[$key]['query'] = clone $setOperation['query'];
		}
	}

	/**
	 * Resets the query to its default state.
	 */
	protected function reset(): void
	{
		$leave = ['connection', 'helper', 'compiler', 'paginationFactory'];

		foreach (array_diff_key(get_class_vars(self::class), array_flip($leave)) as $property => $default) {
			$this->{$property} = $default;
		}
	}

	/**
	 * Returns a new query builder instance.
	 */
	public function newInstance(): self
	{
		return new self($this->connection);
	}

	/**
	 * Sets the query to subquery context.
	 *
	 * @return $this
	 */
	public function inSubqueryContext(): static
	{
		$this->inSubqueryContext = true;

		return $this;
	}

	/**
	 * Sets the pagination factory.
	 */
	public static function setPaginationFactory(Closure|PaginationFactoryInterface $factory): void
	{
		static::$paginationFactory = $factory;
	}

	/**
	 * Gets the pagination factory.
	 */
	public static function getPaginationFactory(): PaginationFactoryInterface
	{
		if (static::$paginationFactory instanceof Closure) {
			$factory = static::$paginationFactory;

			static::$paginationFactory = $factory();
		}

		return static::$paginationFactory;
	}

	/**
	 * Returns the connection instance.
	 */
	public function getConnection(): Connection
	{
		return $this->connection;
	}

	/**
	 * Returns query compiler instance.
	 */
	public function getCompiler(): Compiler
	{
		return $this->compiler;
	}

	/**
	 * Returns the common set operations.
	 */
	public function getCommonTableExpressions(): array
	{
		return $this->commonTableExpressions;
	}

	/**
	 * Returns the set operations.
	 */
	public function getSetOperations(): array
	{
		return $this->setOperations;
	}

	/**
	 * Returns the database table.
	 */
	public function getTable(): null|array|Raw|string|Subquery
	{
		return $this->table;
	}

	/**
	 * Is it a distict select?
	 */
	public function isDistinct(): bool
	{
		return $this->distinct;
	}

	/**
	 * Returns the columns from which we are fetching data.
	 */
	public function getColumns(): array
	{
		return $this->columns;
	}

	/**
	 * Returns WHERE clauses.
	 */
	public function getWheres(): array
	{
		return $this->wheres;
	}

	/**
	 * Returns JOIN clauses.
	 */
	public function getJoins(): array
	{
		return $this->joins;
	}

	/**
	 * Returns GROUP BY clauses.
	 */
	public function getGroupings(): array
	{
		return $this->groupings;
	}

	/**
	 * Returns HAVING clauses.
	 */
	public function getHavings(): array
	{
		return $this->havings;
	}

	/**
	 * Returns ORDER BY clauses.
	 */
	public function getOrderings(): array
	{
		return $this->orderings;
	}

	/**
	 * Returns the limit.
	 */
	public function getLimit(): ?int
	{
		return $this->limit;
	}

	/**
	 * Returns the offset.
	 */
	public function getOffset(): ?int
	{
		return $this->offset;
	}

	/**
	 * Returns the lock.
	 */
	public function getLock(): null|bool|string
	{
		return $this->lock;
	}

	/**
	 * Returns the prefix.
	 */
	public function getPrefix(): ?string
	{
		return $this->prefix;
	}

	/**
	 * Executes the closure if the compiler is of the correct class.
	 *
	 * @return $this
	 */
	public function forCompiler(string $compilerClass, Closure $query): static
	{
		if ($this->compiler instanceof $compilerClass) {
			$query($this);
		}

		return $this;
	}

	/**
	 * Adds a common table expression.
	 *
	 * @return $this
	 */
	public function with(string $name, array $columns, Subquery $query): static
	{
		$this->commonTableExpressions['ctes'][] = [
			'name'    => $name,
			'columns' => $columns,
			'query'   => $query,
		];

		return $this;
	}

	/**
	 * Adds a recursive common table expression.
	 *
	 * @return $this
	 */
	public function withRecursive(string $name, array $columns, Subquery $query): static
	{
		$this->commonTableExpressions['recursive'] = true;

		return $this->with($name, $columns, $query);
	}

	/**
	 * Adds a set operation.
	 *
	 * @return $this
	 */
	protected function setOperation(string $operation): static
	{
		$previous = clone $this;

		$previous->setOperations = [];

		$setOperations = [...$this->setOperations, ['query' => $previous, 'operation' => $operation]];

		$this->reset();

		$this->setOperations = $setOperations;

		return $this;
	}

	/**
	 * Adds a UNION operation.
	 *
	 * @return $this
	 */
	public function union(): static
	{
		return $this->setOperation('UNION');
	}

	/**
	 * Adds a UNION ALL operation.
	 *
	 * @return $this
	 */
	public function unionAll(): static
	{
		return $this->setOperation('UNION ALL');
	}

	/**
	 * Adds a INTERSECT operation.
	 *
	 * @return $this
	 */
	public function intersect(): static
	{
		return $this->setOperation('INTERSECT');
	}

	/**
	 * Adds a INTERSECT ALL operation.
	 *
	 * @return $this
	 */
	public function intersectAll(): static
	{
		return $this->setOperation('INTERSECT ALL');
	}

	/**
	 * Adds a EXCEPT operation.
	 *
	 * @return $this
	 */
	public function except(): static
	{
		return $this->setOperation('EXCEPT');
	}

	/**
	 * Adds a EXCEPT ALL operation.
	 *
	 * @return $this
	 */
	public function exceptAll(): static
	{
		return $this->setOperation('EXCEPT ALL');
	}

	/**
	 * Sets table we want to query.
	 *
	 * @return $this
	 */
	public function table(null|array|Raw|string|Subquery $table): static
	{
		$this->table = $table;

		return $this;
	}

	/**
	 * Alias of Query::table().
	 *
	 * @return $this
	 */
	public function from(null|array|Raw|string|Subquery $table): static
	{
		return $this->table($table);
	}

	/**
	 * Alias of Query::table().
	 *
	 * @return $this
	 */
	public function into(null|array|Raw|string|Subquery $table): static
	{
		return $this->table($table);
	}

	/**
	 * Sets the columns we want to select.
	 *
	 * @return $this
	 */
	public function select(array $columns): static
	{
		$this->columns = $columns;

		return $this;
	}

	/**
	 * Sets the columns we want to select using raw SQL.
	 *
	 * @return $this
	 */
	public function selectRaw(string $sql, array $parameters = []): static
	{
		$this->columns = [new Raw($sql, $parameters)];

		return $this;
	}

	/**
	 * Select distinct?
	 *
	 * @return $this
	 */
	public function distinct(): static
	{
		$this->distinct = true;

		return $this;
	}

	/**
	 * Adds a WHERE clause.
	 *
	 * @return $this
	 */
	public function where(array|Closure|Raw|string $column, ?string $operator = null, mixed $value = null, string $separator = 'AND'): static
	{
		if ($column instanceof Closure) {
			$query = $this->newInstance();

			$column($query);

			$this->wheres[] = [
				'type'      => 'nestedWhere',
				'query'     => $query,
				'separator' => $separator,
			];
		}
		else {
			$this->wheres[] = [
				'type'      => 'where',
				'column'    => $column,
				'operator'  => $operator,
				'value'     => $value,
				'separator' => $separator,
			];
		}

		return $this;
	}

	/**
	 * Adds a OR WHERE clause.
	 *
	 * @return $this
	 */
	public function orWhere(array|Closure|Raw|string $column, ?string $operator = null, mixed $value = null): static
	{
		return $this->where($column, $operator, $value, 'OR');
	}

	/**
	 * Adds a raw WHERE clause.
	 *
	 * @return $this
	 */
	public function whereRaw(array|Raw|string $column, null|array|string $operator = null, ?string $raw = null, string $separator = 'AND'): static
	{
		if ($raw === null) {
			$this->wheres[] = [
				'type'      => 'whereRaw',
				'raw'       => new Raw($column, is_array($operator) ? $operator : []),
				'separator' => $separator,
			];

			return $this;
		}

		return $this->where($column, $operator, new Raw($raw), $separator);
	}

	/**
	 * Adds a raw OR WHERE clause.
	 *
	 * @return $this
	 */
	public function orWhereRaw(array|Raw|string $column, null|array|string $operator = null, ?string $raw = null): static
	{
		return $this->whereRaw($column, $operator, $raw, 'OR');
	}

	/**
	 * Adds a date comparison clause.
	 *
	 * @return $this
	 */
	public function whereDate(string $column, string $operator, DateTimeInterface|string $date, string $separator = 'AND'): static
	{

		$this->wheres[] = [
			'type'      => 'whereDate',
			'column'    => $column,
			'operator'  => $operator,
			'value'     => $date instanceof DateTimeInterface ? $date->format('Y-m-d') : $date,
			'separator' => $separator,
		];

		return $this;
	}

	/**
	 * Adds a date comparison clause.
	 *
	 * @return $this
	 */
	public function orWhereDate(string $column, string $operator, DateTimeInterface|string $date): static
	{
		return $this->whereDate($column, $operator, $date, 'OR');
	}

	/**
	 * Adds a column comparison clause.
	 *
	 * @return $this
	 */
	public function whereColumn(array|string $column1, string $operator, array|string $column2, string $separator = 'AND'): static
	{
		$this->wheres[] = [
			'type'      => 'whereColumn',
			'column1'   => $column1,
			'operator'  => $operator,
			'column2'   => $column2,
			'separator' => $separator,
		];

		return $this;
	}

	/**
	 * Adds a column comparison clause.
	 *
	 * @return $this
	 */
	public function orWhereColumn(array|string $column1, string $operator, array|string $column2): static
	{
		return $this->whereColumn($column1, $operator, $column2, 'OR');
	}

	/**
	 * Adds a vector distance clause.
	 *
	 * @return $this
	 */
	public function whereVectorDistance(string $column, array|string $vector, float $maxDistance = 0.2, VectorMetric $vectorMetric = VectorMetric::COSINE, string $separator = 'AND'): static
	{
		$this->wheres[] = [
			'type'      => 'whereVectorDistance',
			'column'    => $column,
			'vector'    => $vector,
			'distance'  => $maxDistance,
			'metric'    => $vectorMetric,
			'separator' => $separator,
		];

		return $this;
	}

	/**
	 * Adds a vector distance clause.
	 *
	 * @return $this
	 */
	public function orWhereVectorDistance(string $column, array|string $vector, float $maxDistance = 0.2, VectorMetric $vectorMetric = VectorMetric::COSINE): static
	{
		return $this->whereVectorDistance($column, $vector, $maxDistance, $vectorMetric, 'OR');
	}

	/**
	 * Adds a BETWEEN clause.
	 *
	 * @return $this
	 */
	public function between(Raw|string|Subquery $column, mixed $value1, mixed $value2, string $separator = 'AND', bool $not = false): static
	{
		$this->wheres[] = [
			'type'      => 'between',
			'column'    => $column,
			'value1'    => $value1,
			'value2'    => $value2,
			'separator' => $separator,
			'not'       => $not,
		];

		return $this;
	}

	/**
	 * Adds a OR BETWEEN clause.
	 *
	 * @return $this
	 */
	public function orBetween(Raw|string|Subquery $column, mixed $value1, mixed $value2): static
	{
		return $this->between($column, $value1, $value2, 'OR');
	}

	/**
	 * Adds a NOT BETWEEN clause.
	 *
	 * @return $this
	 */
	public function notBetween(Raw|string|Subquery $column, mixed $value1, mixed $value2): static
	{
		return $this->between($column, $value1, $value2, 'AND', true);
	}

	/**
	 * Adds a OR NOT BETWEEN clause.
	 *
	 * @return $this
	 */
	public function orNotBetween(Raw|string|Subquery $column, mixed $value1, mixed $value2): static
	{
		return $this->between($column, $value1, $value2, 'OR', true);
	}

	/**
	 * Adds a date range clause.
	 *
	 * @return $this
	 */
	public function betweenDate(string $column, DateTimeInterface|string $date1, DateTimeInterface|string $date2, string $separator = 'AND', bool $not = false): static
	{
		$this->wheres[] = [
			'type'      => 'betweenDate',
			'column'    => $column,
			'value1'    => $date1 instanceof DateTimeInterface ? $date1->format('Y-m-d') : $date1,
			'value2'    => $date2 instanceof DateTimeInterface ? $date2->format('Y-m-d') : $date2,
			'separator' => $separator,
			'not'       => $not,
		];

		return $this;
	}

	/**
	 * Adds a date range clause.
	 *
	 * @return $this
	 */
	public function orBetweenDate(string $column, DateTimeInterface|string $date1, DateTimeInterface|string $date2): static
	{
		return $this->betweenDate($column, $date1, $date2, 'OR');
	}

	/**
	 * Adds a date range clause.
	 *
	 * @return $this
	 */
	public function notBetweenDate(string $column, DateTimeInterface|string $date1, DateTimeInterface|string $date2): static
	{
		return $this->betweenDate($column, $date1, $date2, 'AND', true);
	}

	/**
	 * Adds a date range clause.
	 *
	 * @return $this
	 */
	public function orNotBetweenDate(string $column, DateTimeInterface|string $date1, DateTimeInterface|string $date2): static
	{
		return $this->betweenDate($column, $date1, $date2, 'OR', true);
	}

	/**
	 * Adds a IN clause.
	 *
	 * @return $this
	 */
	public function in(Raw|string|Subquery $column, array|Raw|Subquery $values, string $separator = 'AND', bool $not = false): static
	{
		$this->wheres[] = [
			'type'      => 'in',
			'column'    => $column,
			'values'    => is_array($values) ? $values : [$values],
			'separator' => $separator,
			'not'       => $not,
		];

		return $this;
	}

	/**
	 * Adds a OR IN clause.
	 *
	 * @return $this
	 */
	public function orIn(Raw|string|Subquery $column, array|Raw|Subquery $values): static
	{
		return $this->in($column, $values, 'OR');
	}

	/**
	 * Adds a NOT IN clause.
	 *
	 * @return $this
	 */
	public function notIn(Raw|string|Subquery $column, array|Raw|Subquery $values): static
	{
		return $this->in($column, $values, 'AND', true);
	}

	/**
	 * Adds a OR NOT IN clause.
	 *
	 * @return $this
	 */
	public function orNotIn(Raw|string|Subquery $column, array|Raw|Subquery $values): static
	{
		return $this->in($column, $values, 'OR', true);
	}

	/**
	 * Adds a IS NULL clause.
	 *
	 * @return $this
	 */
	public function isNull(Raw|string|Subquery $column, string $separator = 'AND', bool $not = false): static
	{
		$this->wheres[] = [
			'type'      => 'null',
			'column'    => $column,
			'separator' => $separator,
			'not'       => $not,
		];

		return $this;
	}

	/**
	 * Adds a OR IS NULL clause.
	 *
	 * @return $this
	 */
	public function orIsNull(Raw|string|Subquery $column): static
	{
		return $this->isNull($column, 'OR');
	}

	/**
	 * Adds a IS NOT NULL clause.
	 *
	 * @return $this
	 */
	public function isNotNull(Raw|string|Subquery $column): static
	{
		return $this->isNull($column, 'AND', true);
	}

	/**
	 * Adds a OR IS NOT NULL clause.
	 *
	 * @return $this
	 */
	public function orIsNotNull(Raw|string|Subquery $column): static
	{
		return $this->isNull($column, 'OR', true);
	}

	/**
	 * Adds a EXISTS clause.
	 *
	 * @return $this
	 */
	public function exists(Subquery $query, string $separator = 'AND', bool $not = false): static
	{
		$this->wheres[] = [
			'type'      => 'exists',
			'query'     => $query,
			'separator' => $separator,
			'not'       => $not,
		];

		return $this;
	}

	/**
	 * Adds a OR EXISTS clause.
	 *
	 * @return $this
	 */
	public function orExists(Subquery $query): static
	{
		return $this->exists($query, 'OR');
	}

	/**
	 * Adds a NOT EXISTS clause.
	 *
	 * @return $this
	 */
	public function notExists(Subquery $query): static
	{
		return $this->exists($query, 'AND', true);
	}

	/**
	 * Adds a OR NOT EXISTS clause.
	 *
	 * @return $this
	 */
	public function orNotExists(Subquery $query): static
	{
		return $this->exists($query, 'OR', true);
	}

	/**
	 * Adds a JOIN clause.
	 *
	 * @return $this
	 */
	public function join(Raw|string|Subquery $table, null|Closure|Raw|string $column1 = null, ?string $operator = null, null|Raw|string $column2 = null, string $type = 'INNER JOIN'): static
	{
		$join = new Join($type, $table);

		if ($column1 instanceof Closure) {
			$column1($join);
		}
		elseif ($column1 !== null) {
			$join->on($column1, $operator, $column2);
		}

		$this->joins[] = $join;

		return $this;
	}

	/**
	 * Adds a raw JOIN clause.
	 *
	 * @return $this
	 */
	public function joinRaw(Raw|string|Subquery $table, Raw|string $column1, string $operator, string $raw, string $type = 'INNER JOIN'): static
	{
		return $this->join($table, $column1, $operator, new Raw($raw), $type);
	}

	/**
	 * Adds a LEFT OUTER JOIN clause.
	 *
	 * @return $this
	 */
	public function leftJoin(Raw|string|Subquery $table, null|Closure|Raw|string $column1 = null, ?string $operator = null, null|Raw|string $column2 = null): static
	{
		return $this->join($table, $column1, $operator, $column2, 'LEFT OUTER JOIN');
	}

	/**
	 * Adds a raw LEFT OUTER JOIN clause.
	 *
	 * @return $this
	 */
	public function leftJoinRaw(Raw|string|Subquery $table, Raw|string $column1, string $operator, string $raw): static
	{
		return $this->joinRaw($table, $column1, $operator, $raw, 'LEFT OUTER JOIN');
	}

	/**
	 * Adds a RIGHT OUTER JOIN clause.
	 *
	 * @return $this
	 */
	public function rightJoin(Raw|string|Subquery $table, null|Closure|Raw|string $column1 = null, ?string $operator = null, null|Raw|string $column2 = null): static
	{
		return $this->join($table, $column1, $operator, $column2, 'RIGHT OUTER JOIN');
	}

	/**
	 * Adds a raw RIGHT OUTER JOIN clause.
	 *
	 * @return $this
	 */
	public function rightJoinRaw(Raw|string|Subquery $table, Raw|string $column1, string $operator, string $raw): static
	{
		return $this->joinRaw($table, $column1, $operator, $raw, 'RIGHT OUTER JOIN');
	}

	/**
	 * Adds a CROSS JOIN clause.
	 *
	 * @return $this
	 */
	public function crossJoin(Raw|string|Subquery $table): static
	{
		return $this->join($table, type: 'CROSS JOIN');
	}

	/**
	 * Adds a LATERAL JOIN clause.
	 *
	 * @return $this
	 */
	public function lateralJoin(Subquery $subquery, string $type = 'LEFT OUTER JOIN'): static
	{
		return $this->join($subquery, new Raw('TRUE'), type: "{$type} LATERAL");
	}

	/**
	 * Adds a GROUP BY clause.
	 *
	 * @return $this
	 */
	public function groupBy(array|string $columns): static
	{
		$this->groupings = is_array($columns) ? $columns : [$columns];

		return $this;
	}

	/**
	 * Adds a HAVING clause.
	 *
	 * @return $this
	 */
	public function having(Raw|string $column, string $operator, mixed $value, string $separator = 'AND'): static
	{
		$this->havings[] = [
			'column'    => $column,
			'operator'  => $operator,
			'value'     => $value,
			'separator' => $separator,
		];

		return $this;
	}

	/**
	 * Adds a raw HAVING clause.
	 *
	 * @return $this
	 */
	public function havingRaw(string $raw, string $operator, mixed $value, string $separator = 'AND'): static
	{
		return $this->having(new Raw($raw), $operator, $value, $separator);
	}

	/**
	 * Adds a OR HAVING clause.
	 *
	 * @return $this
	 */
	public function orHaving(Raw|string $column, string $operator, mixed $value): static
	{
		return $this->having($column, $operator, $value, 'OR');
	}

	/**
	 * Adds a raw OR HAVING clause.
	 *
	 * @return $this
	 */
	public function orHavingRaw(string $raw, string $operator, mixed $value): static
	{
		return $this->havingRaw($raw, $operator, $value, 'OR');
	}

	/**
	 * Adds a ORDER BY clause.
	 *
	 * @return $this
	 */
	public function orderBy(array|Raw|string $columns, string $order = 'ASC'): static
	{
		$this->orderings[] = [
			'column' => is_array($columns) ? $columns : [$columns],
			'order'  => ($order === 'ASC' || $order === 'asc') ? 'ASC' : 'DESC',
		];

		return $this;
	}

	/**
	 * Adds a raw ORDER BY clause.
	 *
	 * @return $this
	 */
	public function orderByRaw(string $raw, array $parameters = [], string $order = 'ASC'): static
	{
		return $this->orderBy(new Raw($raw, $parameters), $order);
	}

	/**
	 * Adds an ascending ORDER BY clause.
	 *
	 * @return $this
	 */
	public function ascending(array|string $columns): static
	{
		return $this->orderBy($columns, 'ASC');
	}

	/**
	 * Adds a raw ascending ORDER BY clause.
	 *
	 * @return $this
	 */
	public function ascendingRaw(string $raw, array $parameters = []): static
	{
		return $this->orderByRaw($raw, $parameters, 'ASC');
	}

	/**
	 * Adds a descending ORDER BY clause.
	 *
	 * @return $this
	 */
	public function descending(array|string $columns): static
	{
		return $this->orderBy($columns, 'DESC');
	}

	/**
	 * Adds a raw descending ORDER BY clause.
	 *
	 * @return $this
	 */
	public function descendingRaw(string $raw, array $parameters = []): static
	{
		return $this->orderByRaw($raw, $parameters, 'DESC');
	}

	/**
	 * Clears the ordering clauses.
	 *
	 * @return $this
	 */
	public function clearOrderings(): static
	{
		$this->orderings = [];

		return $this;
	}

	/**
	 * Adds a LIMIT clause.
	 *
	 * @return $this
	 */
	public function limit(int $limit): static
	{
		$this->limit = (int) $limit;

		return $this;
	}

	/**
	 * Adds a OFFSET clause.
	 *
	 * @return $this
	 */
	public function offset(int $offset): static
	{
		$this->offset = (int) $offset;

		return $this;
	}

	/**
	 * Enable lock.
	 *
	 * @return $this
	 */
	public function lock(bool|string $lock = true): static
	{
		$this->lock = $lock;

		return $this;
	}

	/**
	 * Enable shared lock.
	 *
	 * @return $this
	 */
	public function sharedLock(): static
	{
		return $this->lock(false);
	}

	/**
	 * Adds a query prefix.
	 *
	 * @return $this
	 */
	public function prefix(string $prefix): static
	{
		$this->prefix = "{$prefix} ";

		return $this;
	}

	/**
	 * Executes a SELECT query and returns the first row of the result set or NULL if nothing is found.
	 */
	protected function fetchFirst(mixed ...$fetchMode): mixed
	{
		$query = $this->limit(1)->compiler->select();

		return $this->connection->first($query['sql'], $query['params'], ...$fetchMode);
	}

	/**
	 * Executes a SELECT query and returns the first row of the result set or NULL if nothing is found.
	 *
	 * @return Result|null
	 */
	public function first(): ?object
	{
		return $this->fetchFirst(PDO::FETCH_CLASS, Result::class);
	}

	/**
	 * Executes a SELECT query and returns the first row of the result set or throw an exception if nothing is found.
	 */
	protected function fetchFirstOrThrow(string $exception, mixed ...$fetchMode): mixed
	{
		$query = $this->limit(1)->compiler->select();

		return $this->connection->firstOrThrow($query['sql'], $query['params'], $exception, ...$fetchMode);
	}

	/**
	 * Executes a SELECT query and returns the first row of the result set or throw an exception if nothing is found.
	 *
	 * @return Result
	 */
	public function firstOrThrow(string $exception = NotFoundException::class): object
	{
		return $this->fetchFirstOrThrow($exception, PDO::FETCH_CLASS, Result::class);
	}

	/**
	 * Creates a result set.
	 */
	protected function createResultSet(array $results): ResultSet
	{
		return new ResultSet($results);
	}

	/**
	 * Executes a SELECT query and returns an array or result set containing all of the matching rows.
	 */
	protected function fetchAll(bool $returnResultSet, mixed ...$fetchMode): array|ResultSet
	{
		$query = $this->compiler->select();

		$results = $this->connection->all($query['sql'], $query['params'], ...$fetchMode);

		return $returnResultSet ? $this->createResultSet($results) : $results;
	}

	/**
	 * Executes a SELECT query and returns a result set containing all of the matching rows.
	 *
	 * @return ResultSet<int, Result>
	 */
	public function all(): ResultSet
	{
		return $this->fetchAll(true, PDO::FETCH_CLASS, Result::class);
	}

	/**
	 * Executes a SELECT query and returns the value of the chosen column of the first row of the result set.
	 */
	public function column(?string $column = null): mixed
	{
		if ($column !== null) {
			$this->select([$column]);
		}

		$query = $this->limit(1)->compiler->select();

		return $this->connection->column($query['sql'], $query['params']);
	}

	/**
	 * Executes a SELECT query and returns an array containing the values of the indicated 0-indexed column.
	 */
	public function columns(?string $column = null): array
	{
		if ($column !== null) {
			$this->select([$column]);
		}

		$query = $this->compiler->select();

		return $this->connection->columns($query['sql'], $query['params']);
	}

	/**
	 * Executes a SELECT query and returns an array where the first column is used as keys and the second as values.
	 */
	public function pairs(string $key, string $value): array
	{
		$this->select([$key, $value]);

		$query = $this->compiler->select();

		return $this->connection->pairs($query['sql'], $query['params']);
	}

	/**
	 * Executes a SELECT query and returns a generator that lets you iterate over the results.
	 */
	protected function fetchYield(mixed ...$fetchMode): Generator
	{
		$query = $this->compiler->select();

		yield from $this->connection->yield($query['sql'], $query['params'], ...$fetchMode);
	}

	/**
	 * Executes a SELECT query and returns a generator that lets you iterate over the results.
	 */
	public function yield(): Generator
	{
		yield from $this->fetchYield(PDO::FETCH_CLASS, Result::class);
	}

	/**
	 * Executes a SELECT query and returns a resource that allows you to treat a column value as a byte stream.
	 *
	 * @return resource|null
	 */
	public function blob(string $column)
	{
		$this->select([$column])->limit(1);

		$query = $this->compiler->select();

		return $this->connection->blob($query['sql'], $query['params'], $column);
	}

	/**
	 * Returns the number of records that the query will return.
	 */
	protected function paginationCount(): int
	{
		$clone = (clone $this)->clearOrderings();

		if (empty($this->setOperations) && empty($this->groupings) && $this->distinct === false) {
			return $clone->count();
		}

		return $this->newInstance()->table(new Subquery(static function (&$query) use ($clone): void {
			$query = $clone->inSubqueryContext();
		}, 'count', true))->count();
	}

	/**
	 * Paginates the results using a pagination instance.
	 */
	public function paginate(?int $itemsPerPage = null, array $options = []): ResultSet
	{
		$count = $this->paginationCount();

		$pagination = static::getPaginationFactory()->create($count, $itemsPerPage, $options);

		if ($count > 0) {
			$results = $this->limit($pagination->limit())->offset($pagination->offset())->all();
		}
		else {
			$results = $this->createResultSet([]);
		}

		$results->setPagination($pagination);

		return $results;
	}

	/**
	 * Fetches data in batches and passes them to the processor closure.
	 */
	public function batch(Closure $processor, int $batchSize = 1000, int $offsetStart = 0, ?int $offsetEnd = null): void
	{
		$this->limit($batchSize);

		while (true) {
			$query = clone $this;

			if ($offsetEnd !== null && $offsetStart >= $offsetEnd) {
				break;
			}

			if ($offsetStart !== 0) {
				$query->offset($offsetStart);
			}

			$results = $query->all();

			if (count($results) > 0) {
				$processor($results);

				$offsetStart += $batchSize;
			}
			else {
				break;
			}
		}
	}

	/**
	 * Sets the selected column of the query to the chosen aggreate.
	 * Executes the query and returns the result if not in subquery context.
	 *
	 * @return mixed|void
	 */
	public function aggregate(string $function, array|Raw|string $column)
	{
		$this->select([new Raw(sprintf($function, is_array($column) ? $this->compiler->columns($column) : $this->compiler->column($column)))]);

		if ($this->inSubqueryContext === false) {
			$query = $this->compiler->select();

			return $this->connection->column($query['sql'], $query['params']);
		}
	}

	/**
	 * Returns the minimum value for the chosen column.
	 */
	public function min(Raw|string $column): mixed
	{
		return $this->aggregate('MIN(%s)', $column);
	}

	/**
	 * Returns the maximum value for the chosen column.
	 */
	public function max(Raw|string $column): mixed
	{
		return $this->aggregate('MAX(%s)', $column);
	}

	/**
	 * Returns sum of all the values in the chosen column.
	 */
	public function sum(Raw|string $column): mixed
	{
		return $this->aggregate('SUM(%s)', $column);
	}

	/**
	 * Returns the average value for the chosen column.
	 */
	public function avg(Raw|string $column): mixed
	{
		return $this->aggregate('AVG(%s)', $column);
	}

	/**
	 * Returns the number of rows.
	 */
	public function count(Raw|string $column = '*'): int
	{
		return (int) $this->aggregate('COUNT(%s)', $column);
	}

	/**
	 * Returns the number of distinct values of the chosen column.
	 */
	public function countDistinct(array|Raw|string $column): int
	{
		return (int) $this->aggregate('COUNT(DISTINCT %s)', $column);
	}

	/**
	 * Inserts a single row of data into the chosen table.
	 */
	public function insert(array $values = []): bool
	{
		$query = $this->compiler->insert($values);

		return $this->connection->query($query['sql'], $query['params']);
	}

	/**
	 * Inserts a single row of data into the chosen table and returns the chosen columns of the resulting row.
	 */
	protected function insertAndReturnFirst(array $values = [], array $return = ['*'], mixed ...$fetchMode): mixed
	{
		$query = $this->compiler->insertAndReturn($values, $return);

		return $this->connection->first($query['sql'], $query['params'], ...$fetchMode);
	}

	/**
	 * Inserts a single row of data into the chosen table and returns the chosen columns of the resulting row.
	 */
	public function insertAndReturn(array $values = [], array $return = ['*']): object
	{
		return $this->insertAndReturnFirst($values, $return, PDO::FETCH_CLASS, Result::class);
	}

	/**
	 * Inserts a single row of data into the chosen table and returns the auto increment id.
	 */
	public function insertAndGetId(array $values = [], string $primaryKey = 'id'): false|int
	{
		return $this->helper->insertAndGetId($this, $values, $primaryKey);
	}

	/**
	 * Inserts multiple rows of data into the chosen table.
	 */
	public function insertMultiple(array ...$values): bool
	{
		$query = $this->compiler->insertMultiple(...$values);

		return $this->connection->query($query['sql'], $query['params']);
	}

	/**
	 * Inserts multiple rows of data into the chosen table and returns an array or result set containing all of the inserted rows.
	 */
	protected function insertMultipleAndReturnAll(array $return, array $values, bool $returnResultSet, mixed ...$fetchMode): array|ResultSet
	{
		$query = $this->compiler->insertMultipleAndReturn($return, ...$values);

		$results = $this->connection->all($query['sql'], $query['params'], ...$fetchMode);

		return $returnResultSet ? $this->createResultSet($results) : $results;
	}

	/**
	 * Inserts multiple rows of data into the chosen table and returns a result set containing all of the inserted rows.
	 *
	 * @return ResultSet<int, Result>
	 */
	public function insertMultipleAndReturn(array $return, array ...$values): ResultSet
	{
		return $this->insertMultipleAndReturnAll($return, $values, true, PDO::FETCH_CLASS, Result::class);
	}

	/**
	 * Inserts or updates a row of data into the chosen table.
	 */
	public function insertOrUpdate(array $insertValues, array $updateValues, array $conflictTarget = []): bool
	{
		$query = $this->compiler->insertOrUpdate($insertValues, $updateValues, $conflictTarget);

		return $this->connection->query($query['sql'], $query['params']);
	}

	/**
	 * Updates data from the chosen table.
	 */
	public function update(array $values): int
	{
		$query = $this->compiler->update($values);

		return $this->connection->queryAndCount($query['sql'], $query['params']);
	}

	/**
	 * Updates data from the chosen table and returns an array or result set.
	 */
	protected function updateAndReturnAll(array $values, array $return, bool $returnResultSet, mixed ...$fetchMode): array|ResultSet
	{
		$query = $this->compiler->updateAndReturn($values, $return);

		$results = $this->connection->all($query['sql'], $query['params'], ...$fetchMode);

		return $returnResultSet ? $this->createResultSet($results) : $results;
	}

	/**
	 * Updates data from the chosen table and returns a result set.
	 *
	 * @return ResultSet<int, Result>
	 */
	public function updateAndReturn(array $values, array $return = ['*']): ResultSet
	{
		return $this->updateAndReturnAll($values, $return, true, PDO::FETCH_CLASS, Result::class);
	}

	/**
	 * Increments column value.
	 */
	public function increment(string $column, int $increment = 1): int
	{
		return $this->update([$column => new Raw("{$this->compiler->escapeIdentifier($column)} + " . (int) $increment)]);
	}

	/**
	 * Decrements column value.
	 */
	public function decrement(string $column, int $decrement = 1): int
	{
		return $this->update([$column => new Raw("{$this->compiler->escapeIdentifier($column)} - " . (int) $decrement)]);
	}

	/**
	 * Deletes data from the chosen table.
	 */
	public function delete(): int
	{
		$query = $this->compiler->delete();

		return $this->connection->queryAndCount($query['sql'], $query['params']);
	}
}
