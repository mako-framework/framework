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
	 *
	 * @var \mako\database\query\helpers\HelperInterface
	 */
	protected $helper;

	/**
	 * Query compiler.
	 *
	 * @var \mako\database\query\compilers\Compiler
	 */
	protected $compiler;

	/**
	 * Database table.
	 *
	 * @var array|\mako\database\query\Raw|\mako\database\query\Subquery|string|null
	 */
	protected $table;

	/**
	 * Select distinct?
	 *
	 * @var bool
	 */
	protected $distinct = false;

	/**
	 * Common table expressions.
	 *
	 * @var array
	 */
	protected $commonTableExpressions = ['recursive' => false, 'ctes' => []];

	/**
	 * Set operations.
	 *
	 * @var array
	 */
	protected $setOperations = [];

	/**
	 * Columns from which we are fetching data.
	 *
	 * @var array
	 */
	protected $columns = ['*'];

	/**
	 * WHERE clauses.
	 *
	 * @var array
	 */
	protected $wheres = [];

	/**
	 * JOIN clauses.
	 *
	 * @var array
	 */
	protected $joins = [];

	/**
	 * GROUP BY clauses.
	 *
	 * @var array
	 */
	protected $groupings = [];

	/**
	 * HAVING clauses.
	 *
	 * @var array
	 */
	protected $havings = [];

	/**
	 * ORDER BY clauses.
	 *
	 * @var array
	 */
	protected $orderings = [];

	/**
	 * Limit.
	 *
	 * @var int|null
	 */
	protected $limit = null;

	/**
	 * Offset.
	 *
	 * @var int|null
	 */
	protected $offset = null;

	/**
	 * Lock.
	 *
	 * @var bool|string|null
	 */
	protected $lock = null;

	/**
	 * Prefix.
	 *
	 * @var string|null
	 */
	protected $prefix = null;

	/**
	 * Is the query in subquery context?
	 *
	 * @var bool
	 */
	protected $inSubqueryContext = false;

	/**
	 * Pagination factory.
	 *
	 * @var \Closure|\mako\pagination\PaginationFactoryInterface
	 */
	protected static $paginationFactory;

	/**
	 * Constructor.
	 *
	 * @param \mako\database\connections\Connection $connection Database connection
	 */
	public function __construct(
		protected Connection $connection
	)
	{
		$this->helper = $connection->getQueryBuilderHelper();

		$this->compiler = $connection->getQueryCompiler($this);
	}

	/**
	 * Create a fresh compiler instance and clone set operation queries when we clone the query.
	 */
	public function __clone()
	{
		$this->compiler = $this->connection->getQueryCompiler($this);

		foreach($this->setOperations as $key => $setOperation)
		{
			$this->setOperations[$key]['query'] = clone $setOperation['query'];
		}
	}

	/**
	 * Resets the query to its default state.
	 */
	protected function reset(): void
	{
		$leave = ['connection', 'helper', 'compiler', 'paginationFactory'];

		foreach(array_diff_key(get_class_vars(self::class), array_flip($leave)) as $property => $default)
		{
			$this->$property = $default;
		}
	}

	/**
	 * Returns a new query builder instance.
	 *
	 * @return self
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
	public function inSubqueryContext()
	{
		$this->inSubqueryContext = true;

		return $this;
	}

	/**
	 * Sets the pagination factory.
	 *
	 * @param \Closure|\mako\pagination\PaginationFactoryInterface $factory Pagination factory
	 */
	public static function setPaginationFactory(Closure|PaginationFactoryInterface $factory): void
	{
		static::$paginationFactory = $factory;
	}

	/**
	 * Gets the pagination factory.
	 *
	 * @return \mako\pagination\PaginationFactoryInterface
	 */
	public static function getPaginationFactory(): PaginationFactoryInterface
	{
		if(static::$paginationFactory instanceof Closure)
		{
			$factory = static::$paginationFactory;

			static::$paginationFactory = $factory();
		}

		return static::$paginationFactory;
	}

	/**
	 * Returns the connection instance.
	 *
	 * @return \mako\database\connections\Connection
	 */
	public function getConnection()
	{
		return $this->connection;
	}

	/**
	 * Returns query compiler instance.
	 *
	 * @return \mako\database\query\compilers\Compiler
	 */
	public function getCompiler()
	{
		return $this->compiler;
	}

	/**
	 * Returns the common set operations.
	 *
	 * @return array
	 */
	public function getCommonTableExpressions(): array
	{
		return $this->commonTableExpressions;
	}

	/**
	 * Returns the set operations.
	 *
	 * @return array
	 */
	public function getSetOperations(): array
	{
		return $this->setOperations;
	}

	/**
	 * Returns the database table.
	 *
	 * @return array|\mako\database\query\Raw|\mako\database\query\Subquery|string|null
	 */
	public function getTable(): array|Raw|Subquery|string|null
	{
		return $this->table;
	}

	/**
	 * Is it a distict select?
	 *
	 * @return bool
	 */
	public function isDistinct(): bool
	{
		return $this->distinct;
	}

	/**
	 * Returns the columns from which we are fetching data.
	 *
	 * @return array
	 */
	public function getColumns(): array
	{
		return $this->columns;
	}

	/**
	 * Returns WHERE clauses.
	 *
	 * @return array
	 */
	public function getWheres(): array
	{
		return $this->wheres;
	}

	/**
	 * Returns JOIN clauses.
	 *
	 * @return array
	 */
	public function getJoins(): array
	{
		return $this->joins;
	}

	/**
	 * Returns GROUP BY clauses.
	 *
	 * @return array
	 */
	public function getGroupings(): array
	{
		return $this->groupings;
	}

	/**
	 * Returns HAVING clauses.
	 *
	 * @return array
	 */
	public function getHavings(): array
	{
		return $this->havings;
	}

	/**
	 * Returns ORDER BY clauses.
	 *
	 * @return array
	 */
	public function getOrderings(): array
	{
		return $this->orderings;
	}

	/**
	 * Returns the limit.
	 *
	 * @return int|null
	 */
	public function getLimit(): ?int
	{
		return $this->limit;
	}

	/**
	 * Returns the offset.
	 *
	 * @return int|null
	 */
	public function getOffset(): ?int
	{
		return $this->offset;
	}

	/**
	 * Returns the lock.
	 *
	 * @return bool|string|null
	 */
	public function getLock(): bool|string|null
	{
		return $this->lock;
	}

	/**
	 * Returns the prefix.
	 *
	 * @return string|null
	 */
	public function getPrefix(): ?string
	{
		return $this->prefix;
	}

	/**
	 * Executes the closure if the compiler is of the correct class.
	 *
	 * @param  string   $compilerClass Compiler class name
	 * @param  \Closure $query         Closure
	 * @return $this
	 */
	public function forCompiler(string $compilerClass, Closure $query)
	{
		if($this->compiler instanceof $compilerClass)
		{
			$query($this);
		}

		return $this;
	}

	/**
	 * Adds a common table expression.
	 *
	 * @param  string                        $name    Table name
	 * @param  array                         $columns Column names
	 * @param  \mako\database\query\Subquery $query   Query
	 * @return $this
	 */
	public function with(string $name, array $columns, Subquery $query)
	{
		$this->commonTableExpressions['ctes'][] =
		[
			'name'    => $name,
			'columns' => $columns,
			'query'   => $query,
		];

		return $this;
	}

	/**
	 * Adds a recursive common table expression.
	 *
	 * @param  string                        $name    Table name
	 * @param  array                         $columns Column names
	 * @param  \mako\database\query\Subquery $query   Query
	 * @return $this
	 */
	public function withRecursive(string $name, array $columns, Subquery $query)
	{
		$this->commonTableExpressions['recursive'] = true;

		return $this->with($name, $columns, $query);
	}

	/**
	 * Adds a set operation.
	 *
	 * @param  string $operation Operation
	 * @return $this
	 */
	protected function setOperation(string $operation)
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
	public function union()
	{
		return $this->setOperation('UNION');
	}

	/**
	 * Adds a UNION ALL operation.
	 *
	 * @return $this
	 */
	public function unionAll()
	{
		return $this->setOperation('UNION ALL');
	}

	/**
	 * Adds a INTERSECT operation.
	 *
	 * @return $this
	 */
	public function intersect()
	{
		return $this->setOperation('INTERSECT');
	}

	/**
	 * Adds a INTERSECT ALL operation.
	 *
	 * @return $this
	 */
	public function intersectAll()
	{
		return $this->setOperation('INTERSECT ALL');
	}

	/**
	 * Adds a EXCEPT operation.
	 *
	 * @return $this
	 */
	public function except()
	{
		return $this->setOperation('EXCEPT');
	}

	/**
	 * Adds a EXCEPT ALL operation.
	 *
	 * @return $this
	 */
	public function exceptAll()
	{
		return $this->setOperation('EXCEPT ALL');
	}

	/**
	 * Sets table we want to query.
	 *
	 * @param  array|\mako\database\query\Raw|\mako\database\query\Subquery|string|null $table Database table or subquery
	 * @return $this
	 */
	public function table(array|Raw|Subquery|string|null $table)
	{
		$this->table = $table;

		return $this;
	}

	/**
	 * Alias of Query::table().
	 *
	 * @param  array|\mako\database\query\Raw|\mako\database\query\Subquery|string|null $table Database table or subquery
	 * @return $this
	 */
	public function from(array|Raw|Subquery|string|null $table)
	{
		return $this->table($table);
	}

	/**
	 * Alias of Query::table().
	 *
	 * @param  array|\mako\database\query\Raw|\mako\database\query\Subquery|string|null $table Database table or subquery
	 * @return $this
	 */
	public function into(array|Raw|Subquery|string|null $table)
	{
		return $this->table($table);
	}

	/**
	 * Sets the columns we want to select.
	 *
	 * @param  array $columns Array of columns we want to select from
	 * @return $this
	 */
	public function select(array $columns)
	{
		$this->columns = $columns;

		return $this;
	}

	/**
	 * Sets the columns we want to select using raw SQL.
	 *
	 * @param  string $sql        Raw sql
	 * @param  array  $parameters Parameters
	 * @return $this
	 */
	public function selectRaw(string $sql, array $parameters = [])
	{
		$this->columns = [new Raw($sql, $parameters)];

		return $this;
	}

	/**
	 * Select distinct?
	 *
	 * @return $this
	 */
	public function distinct()
	{
		$this->distinct = true;

		return $this;
	}

	/**
	 * Adds a WHERE clause.
	 *
	 * @param  array|\Closure|\mako\database\query\Raw|string $column    Column name or an array of column names
	 * @param  string|null                                    $operator  Operator
	 * @param  mixed                                          $value     Value
	 * @param  string                                         $separator Clause separator
	 * @return $this
	 */
	public function where(array|Closure|Raw|string $column, ?string $operator = null, mixed $value = null, string $separator = 'AND')
	{
		if($column instanceof Closure)
		{
			$query = $this->newInstance();

			$column($query);

			$this->wheres[] =
			[
				'type'      => 'nestedWhere',
				'query'     => $query,
				'separator' => $separator,
			];
		}
		else
		{
			$this->wheres[] =
			[
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
	 * Adds a raw WHERE clause.
	 *
	 * @param  array|\mako\database\query\Raw|string $column    Column name, an array of column names or raw SQL
	 * @param  array|string|null                     $operator  Operator or parameters
	 * @param  string|null                           $raw       Raw SQL
	 * @param  string                                $separator Clause separator
	 * @return $this
	 */
	public function whereRaw(array|Raw|string $column, array|string|null $operator = null, ?string $raw = null, string $separator = 'AND')
	{
		if($raw === null)
		{
			$this->wheres[] =
			[
				'type'      => 'whereRaw',
				'raw'       => new Raw($column, is_array($operator) ? $operator : []),
				'separator' => $separator,
			];

			return $this;
		}

		return $this->where($column, $operator, new Raw($raw), $separator);
	}

	/**
	 * Adds a OR WHERE clause.
	 *
	 * @param  array|\Closure|\mako\database\query\Raw|string $column   Column name or an array of column names
	 * @param  string|null                                    $operator Operator
	 * @param  mixed                                          $value    Value
	 * @return $this
	 */
	public function orWhere(array|Closure|Raw|string $column, ?string $operator = null, mixed $value = null)
	{
		return $this->where($column, $operator, $value, 'OR');
	}

	/**
	 * Adds a raw OR WHERE clause.
	 *
	 * @param  array|\mako\database\query\Raw|string $column   Column name, and array of column names or raw SQL
	 * @param  array|string|null                     $operator Operator or parameters
	 * @param  string|null                           $raw      Raw SQL
	 * @return $this
	 */
	public function orWhereRaw(array|Raw|string $column, array|string|null $operator = null, ?string $raw = null)
	{
		return $this->whereRaw($column, $operator, $raw, 'OR');
	}

	/**
	 * Adds a date comparison clause.
	 *
	 * @param  string                    $column    Column name
	 * @param  string                    $operator  Operator
	 * @param  \DateTimeInterface|string $date      Date
	 * @param  string                    $separator Separator
	 * @return $this
	 */
	public function whereDate(string $column, string $operator, DateTimeInterface|string $date, string $separator = 'AND')
	{

		$this->wheres[] =
		[
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
	 * @param  string                    $column   Column name
	 * @param  string                    $operator Operator
	 * @param  \DateTimeInterface|string $date     Date
	 * @return $this
	 */
	public function orWhereDate(string $column, string $operator, DateTimeInterface|string $date)
	{
		return $this->whereDate($column, $operator, $date, 'OR');
	}

	/**
	 * Adds a column comparison clause.
	 *
	 * @param  array|string $column1   Column name of array of column names
	 * @param  string       $operator  Operator
	 * @param  array|string $column2   Column name of array of column names
	 * @param  string       $separator Separator
	 * @return $this
	 */
	public function whereColumn(array|string $column1, string $operator, array|string $column2, string $separator = 'AND')
	{
		$this->wheres[] =
		[
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
	 * @param  array|string $column1  Column name of array of column names
	 * @param  string       $operator Operator
	 * @param  array|string $column2  Column name of array of column names
	 * @return $this
	 */
	public function orWhereColumn(array|string $column1, string $operator, array|string $column2)
	{
		return $this->whereColumn($column1, $operator, $column2, 'OR');
	}

	/**
	 * Adds a BETWEEN clause.
	 *
	 * @param  mixed  $column    Column name
	 * @param  mixed  $value1    First value
	 * @param  mixed  $value2    Second value
	 * @param  string $separator Clause separator
	 * @param  bool   $not       Not between?
	 * @return $this
	 */
	public function between(mixed $column, mixed $value1, mixed $value2, string $separator = 'AND', bool $not = false)
	{
		$this->wheres[] =
		[
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
	 * @param  mixed $column Column name
	 * @param  mixed $value1 First value
	 * @param  mixed $value2 Second value
	 * @return $this
	 */
	public function orBetween(mixed $column, mixed $value1, mixed $value2)
	{
		return $this->between($column, $value1, $value2, 'OR');
	}

	/**
	 * Adds a NOT BETWEEN clause.
	 *
	 * @param  mixed $column Column name
	 * @param  mixed $value1 First value
	 * @param  mixed $value2 Second value
	 * @return $this
	 */
	public function notBetween(mixed $column, mixed $value1, mixed $value2)
	{
		return $this->between($column, $value1, $value2, 'AND', true);
	}

	/**
	 * Adds a OR NOT BETWEEN clause.
	 *
	 * @param  mixed $column Column name
	 * @param  mixed $value1 First value
	 * @param  mixed $value2 Second value
	 * @return $this
	 */
	public function orNotBetween(mixed $column, mixed $value1, mixed $value2)
	{
		return $this->between($column, $value1, $value2, 'OR', true);
	}

	/**
	 * Adds a date range clause.
	 *
	 * @param  string                    $column    Column name
	 * @param  \DateTimeInterface|string $date1     First date
	 * @param  \DateTimeInterface|string $date2     Second date
	 * @param  string                    $separator Separator
	 * @param  bool                      $not       Not between?
	 * @return $this
	 */
	public function betweenDate(string $column, DateTimeInterface|string $date1, DateTimeInterface|string $date2, string $separator = 'AND', bool $not = false)
	{
		$this->wheres[] =
		[
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
	 * @param  string                    $column Column name
	 * @param  \DateTimeInterface|string $date1  First date
	 * @param  \DateTimeInterface|string $date2  Second date
	 * @return $this
	 */
	public function orBetweenDate(string $column, DateTimeInterface|string $date1, DateTimeInterface|string $date2)
	{
		return $this->betweenDate($column, $date1, $date2, 'OR');
	}

	/**
	 * Adds a date range clause.
	 *
	 * @param  string                    $column Column name
	 * @param  \DateTimeInterface|string $date1  First date
	 * @param  \DateTimeInterface|string $date2  Second date
	 * @return $this
	 */
	public function notBetweenDate(string $column, DateTimeInterface|string $date1, DateTimeInterface|string $date2)
	{
		return $this->betweenDate($column, $date1, $date2, 'AND', true);
	}

	/**
	 * Adds a date range clause.
	 *
	 * @param  string                    $column Column name
	 * @param  \DateTimeInterface|string $date1  First date
	 * @param  \DateTimeInterface|string $date2  Second date
	 * @return $this
	 */
	public function orNotBetweenDate(string $column, DateTimeInterface|string $date1, DateTimeInterface|string $date2)
	{
		return $this->betweenDate($column, $date1, $date2, 'OR', true);
	}

	/**
	 * Adds a IN clause.
	 *
	 * @param  mixed                                                        $column    Column name
	 * @param  array|\mako\database\query\Raw|\mako\database\query\Subquery $values    Array of values or Subquery
	 * @param  string                                                       $separator Clause separator
	 * @param  bool                                                         $not       Not in?
	 * @return $this
	 */
	public function in(mixed $column, array|Raw|Subquery $values, string $separator = 'AND', bool $not = false)
	{
		$this->wheres[] =
		[
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
	 * @param  mixed                                                        $column Column name
	 * @param  array|\mako\database\query\Raw|\mako\database\query\Subquery $values Array of values or Subquery
	 * @return $this
	 */
	public function orIn(mixed $column, array|Raw|Subquery $values)
	{
		return $this->in($column, $values, 'OR');
	}

	/**
	 * Adds a NOT IN clause.
	 *
	 * @param  mixed                                                        $column Column name
	 * @param  array|\mako\database\query\Raw|\mako\database\query\Subquery $values Array of values or Subquery
	 * @return $this
	 */
	public function notIn(mixed $column, array|Raw|Subquery $values)
	{
		return $this->in($column, $values, 'AND', true);
	}

	/**
	 * Adds a OR NOT IN clause.
	 *
	 * @param  mixed                                                        $column Column name
	 * @param  array|\mako\database\query\Raw|\mako\database\query\Subquery $values Array of values or Subquery
	 * @return $this
	 */
	public function orNotIn(mixed $column, array|Raw|Subquery $values)
	{
		return $this->in($column, $values, 'OR', true);
	}

	/**
	 * Adds a IS NULL clause.
	 *
	 * @param  mixed  $column    Column name
	 * @param  string $separator Clause separator
	 * @param  bool   $not       Not in?
	 * @return $this
	 */
	public function isNull(mixed $column, string $separator = 'AND', bool $not = false)
	{
		$this->wheres[] =
		[
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
	 * @param  mixed $column Column name
	 * @return $this
	 */
	public function orIsNull(mixed $column)
	{
		return $this->isNull($column, 'OR');
	}

	/**
	 * Adds a IS NOT NULL clause.
	 *
	 * @param  mixed $column Column name
	 * @return $this
	 */
	public function isNotNull(mixed $column)
	{
		return $this->isNull($column, 'AND', true);
	}

	/**
	 * Adds a OR IS NOT NULL clause.
	 *
	 * @param  mixed $column Column name
	 * @return $this
	 */
	public function orIsNotNull(mixed $column)
	{
		return $this->isNull($column, 'OR', true);
	}

	/**
	 * Adds a EXISTS clause.
	 *
	 * @param  \mako\database\query\Subquery $query     Subquery
	 * @param  string                        $separator Clause separator
	 * @param  bool                          $not       Not exists?
	 * @return $this
	 */
	public function exists(Subquery $query, string $separator = 'AND', bool $not = false)
	{
		$this->wheres[] =
		[
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
	 * @param  \mako\database\query\Subquery $query Subquery
	 * @return $this
	 */
	public function orExists(Subquery $query)
	{
		return $this->exists($query, 'OR');
	}

	/**
	 * Adds a NOT EXISTS clause.
	 *
	 * @param  \mako\database\query\Subquery $query Subquery
	 * @return $this
	 */
	public function notExists(Subquery $query)
	{
		return $this->exists($query, 'AND', true);
	}

	/**
	 * Adds a OR NOT EXISTS clause.
	 *
	 * @param  \mako\database\query\Subquery $query Subquery
	 * @return $this
	 */
	public function orNotExists(Subquery $query)
	{
		return $this->exists($query, 'OR', true);
	}

	/**
	 * Adds a JOIN clause.
	 *
	 * @param  \mako\database\query\Raw|\mako\database\query\Subquery|string $table    Table name
	 * @param  \Closure|\mako\database\query\Raw|string|null                 $column1  Column name
	 * @param  string|null                                                   $operator Operator
	 * @param  \mako\database\query\Raw|string|null                          $column2  Column name
	 * @param  string                                                        $type     Join type
	 * @return $this
	 */
	public function join(Raw|Subquery|string $table, Closure|Raw|string|null $column1 = null, ?string $operator = null, Raw|string|null $column2 = null, string $type = 'INNER JOIN')
	{
		$join = new Join($type, $table);

		if($column1 instanceof Closure)
		{
			$column1($join);
		}
		elseif($column1 !== null)
		{
			$join->on($column1, $operator, $column2);
		}

		$this->joins[] = $join;

		return $this;
	}

	/**
	 * Adds a raw JOIN clause.
	 *
	 * @param  \mako\database\query\Raw|\mako\database\query\Subquery|string $table    Table name
	 * @param  \mako\database\query\Raw|string                               $column1  Column name
	 * @param  string                                                        $operator Operator
	 * @param  string                                                        $raw      Raw SQL
	 * @param  string                                                        $type     Join type
	 * @return $this
	 */
	public function joinRaw(Raw|Subquery|string $table, Raw|string $column1, string $operator, string $raw, string $type = 'INNER JOIN')
	{
		return $this->join($table, $column1, $operator, new Raw($raw), $type);
	}

	/**
	 * Adds a LEFT OUTER JOIN clause.
	 *
	 * @param  \mako\database\query\Raw|\mako\database\query\Subquery|string $table    Table name
	 * @param  \Closure|\mako\database\query\Raw|string|null                 $column1  Column name
	 * @param  string|null                                                   $operator Operator
	 * @param  \mako\database\query\Raw|string|null                          $column2  Column name
	 * @return $this
	 */
	public function leftJoin(Raw|Subquery|string $table, Closure|Raw|string|null $column1 = null, ?string $operator = null, Raw|string|null $column2 = null)
	{
		return $this->join($table, $column1, $operator, $column2, 'LEFT OUTER JOIN');
	}

	/**
	 * Adds a raw LEFT OUTER JOIN clause.
	 *
	 * @param  \mako\database\query\Raw|\mako\database\query\Subquery|string $table    Table name
	 * @param  \mako\database\query\Raw|string                               $column1  Column name
	 * @param  string                                                        $operator Operator
	 * @param  string                                                        $raw      Raw SQL
	 * @return $this
	 */
	public function leftJoinRaw(Raw|Subquery|string $table, Raw|string $column1, string $operator, string $raw)
	{
		return $this->joinRaw($table, $column1, $operator, $raw, 'LEFT OUTER JOIN');
	}

	/**
	 * Adds a RIGHT OUTER JOIN clause.
	 *
	 * @param  \mako\database\query\Raw|\mako\database\query\Subquery|string $table    Table name
	 * @param  \Closure|\mako\database\query\Raw|string|null                 $column1  Column name
	 * @param  string|null                                                   $operator Operator
	 * @param  \mako\database\query\Raw|string|null                          $column2  Column name
	 * @return $this
	 */
	public function rightJoin(Raw|Subquery|string $table, Closure|Raw|string|null $column1 = null, ?string $operator = null, Raw|string|null $column2 = null)
	{
		return $this->join($table, $column1, $operator, $column2, 'RIGHT OUTER JOIN');
	}

	/**
	 * Adds a raw RIGHT OUTER JOIN clause.
	 *
	 * @param  \mako\database\query\Raw|\mako\database\query\Subquery|string $table    Table name
	 * @param  \mako\database\query\Raw|string                               $column1  Column name
	 * @param  string                                                        $operator Operator
	 * @param  string                                                        $raw      Raw SQL
	 * @return $this
	 */
	public function rightJoinRaw(Raw|Subquery|string $table, Raw|string $column1, string $operator, string $raw)
	{
		return $this->joinRaw($table, $column1, $operator, $raw, 'RIGHT OUTER JOIN');
	}

	/**
	 * Adds a CROSS JOIN clause.
	 *
	 * @param  \mako\database\query\Subquery|string $table Table name or subquery
	 * @return $this
	 */
	public function crossJoin(Raw|Subquery|string $table)
	{
		return $this->join($table, type: 'CROSS JOIN');
	}

	/**
	 * Adds a LATERAL JOIN clause.
	 *
	 * @param  \mako\database\query\Subquery $subquery Subquery
	 * @param  string                        $type     Join type
	 * @return $this
	 */
	public function lateralJoin(Subquery $subquery, string $type = 'LEFT OUTER JOIN')
	{
		return $this->join($subquery, new Raw('TRUE'), type: "{$type} LATERAL");
	}

	/**
	 * Adds a GROUP BY clause.
	 *
	 * @param  array|string $columns Column name or array of column names
	 * @return $this
	 */
	public function groupBy(array|string $columns)
	{
		$this->groupings = is_array($columns) ? $columns : [$columns];

		return $this;
	}

	/**
	 * Adds a HAVING clause.
	 *
	 * @param  \mako\database\query\Raw|string $column    Column name
	 * @param  string                          $operator  Operator
	 * @param  mixed                           $value     Value
	 * @param  string                          $separator Clause separator
	 * @return $this
	 */
	public function having(Raw|string $column, string $operator, mixed $value, string $separator = 'AND')
	{
		$this->havings[] =
		[
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
	 * @param  string $raw       Raw SQL
	 * @param  string $operator  Operator
	 * @param  mixed  $value     Value
	 * @param  string $separator Clause separator
	 * @return $this
	 */
	public function havingRaw(string $raw, string $operator, mixed $value, string $separator = 'AND')
	{
		return $this->having(new Raw($raw), $operator, $value, $separator);
	}

	/**
	 * Adds a OR HAVING clause.
	 *
	 * @param  \mako\database\query\Raw|string $column   Column name
	 * @param  string                          $operator Operator
	 * @param  mixed                           $value    Value
	 * @return $this
	 */
	public function orHaving(Raw|string $column, string $operator, mixed $value)
	{
		return $this->having($column, $operator, $value, 'OR');
	}

	/**
	 * Adds a raw OR HAVING clause.
	 *
	 * @param  string $raw      Raw SQL
	 * @param  string $operator Operator
	 * @param  mixed  $value    Value
	 * @return $this
	 */
	public function orHavingRaw(string $raw, string $operator, mixed $value)
	{
		return $this->havingRaw($raw, $operator, $value, 'OR');
	}

	/**
	 * Adds a ORDER BY clause.
	 *
	 * @param  array|\mako\database\query\Raw|string $columns Column name or array of column names
	 * @param  string                                $order   Sorting order
	 * @return $this
	 */
	public function orderBy(array|Raw|string $columns, string $order = 'ASC')
	{
		$this->orderings[] =
		[
			'column' => is_array($columns) ? $columns : [$columns],
			'order'  => ($order === 'ASC' || $order === 'asc') ? 'ASC' : 'DESC',
		];

		return $this;
	}

	/**
	 * Adds a raw ORDER BY clause.
	 *
	 * @param  string $raw        Raw SQL
	 * @param  array  $parameters Parameters
	 * @param  string $order      Sorting order
	 * @return $this
	 */
	public function orderByRaw(string $raw, array $parameters = [], string $order = 'ASC')
	{
		return $this->orderBy(new Raw($raw, $parameters), $order);
	}

	/**
	 * Adds an ascending ORDER BY clause.
	 *
	 * @param  array|string $columns Column name or array of column names
	 * @return $this
	 */
	public function ascending(array|string $columns)
	{
		return $this->orderBy($columns, 'ASC');
	}

	/**
	 * Adds a raw ascending ORDER BY clause.
	 *
	 * @param  string $raw        Raw SQL
	 * @param  array  $parameters Parameters
	 * @return $this
	 */
	public function ascendingRaw(string $raw, array $parameters = [])
	{
		return $this->orderByRaw($raw, $parameters, 'ASC');
	}

	/**
	 * Adds a descending ORDER BY clause.
	 *
	 * @param  array|string $columns Column name or array of column names
	 * @return $this
	 */
	public function descending(array|string $columns)
	{
		return $this->orderBy($columns, 'DESC');
	}

	/**
	 * Adds a raw descending ORDER BY clause.
	 *
	 * @param  string $raw        Raw SQL
	 * @param  array  $parameters Parameters
	 * @return $this
	 */
	public function descendingRaw(string $raw, array $parameters = [])
	{
		return $this->orderByRaw($raw, $parameters, 'DESC');
	}

	/**
	 * Clears the ordering clauses.
	 *
	 * @return $this
	 */
	public function clearOrderings()
	{
		$this->orderings = [];

		return $this;
	}

	/**
	 * Adds a LIMIT clause.
	 *
	 * @param  int   $limit Limit
	 * @return $this
	 */
	public function limit(int $limit)
	{
		$this->limit = (int) $limit;

		return $this;
	}

	/**
	 * Adds a OFFSET clause.
	 *
	 * @param  int   $offset Offset
	 * @return $this
	 */
	public function offset(int $offset)
	{
		$this->offset = (int) $offset;

		return $this;
	}

	/**
	 * Enable lock.
	 *
	 * @param  bool|string $lock TRUE for exclusive, FALSE for shared and string for custom
	 * @return $this
	 */
	public function lock(bool|string $lock = true)
	{
		$this->lock = $lock;

		return $this;
	}

	/**
	 * Enable shared lock.
	 *
	 * @return $this
	 */
	public function sharedLock()
	{
		return $this->lock(false);
	}

	/**
	 * Adds a query prefix.
	 *
	 * @param  string $prefix Prefix
	 * @return $this
	 */
	public function prefix(string $prefix)
	{
		$this->prefix = "{$prefix} ";

		return $this;
	}

	/**
	 * Executes a SELECT query and returns the first row of the result set or NULL if nothing is found.
	 *
	 * @param  mixed ...$fetchMode Fetch mode
	 * @return mixed
	 */
	protected function fetchFirst(mixed ...$fetchMode): mixed
	{
		$query = $this->limit(1)->compiler->select();

		return $this->connection->first($query['sql'], $query['params'], ...$fetchMode);
	}

	/**
	 * Executes a SELECT query and returns the first row of the result set or NULL if nothing is found.
	 *
	 * @return mixed
	 */
	public function first(): mixed
	{
		return $this->fetchFirst(PDO::FETCH_CLASS, Result::class);
	}

	/**
	 * Executes a SELECT query and returns the first row of the result set or throw an exception if nothing is found.
	 *
	 * @param  string $exception    Exception class
	 * @param  mixed  ...$fetchMode Fetch mode
	 * @return mixed
	 */
	protected function fetchFirstOrThrow(string $exception, mixed ...$fetchMode): mixed
	{
		$query = $this->limit(1)->compiler->select();

		return $this->connection->firstOrThrow($query['sql'], $query['params'], $exception, ...$fetchMode);
	}

	/**
	 * Executes a SELECT query and returns the first row of the result set or throw an exception if nothing is found.
	 *
	 * @param  string $exception Exception class
	 * @return mixed
	 */
	public function firstOrThrow(string $exception = NotFoundException::class): mixed
	{
		return $this->fetchFirstOrThrow($exception, PDO::FETCH_CLASS, Result::class);
	}

	/**
	 * Creates a result set.
	 *
	 * @param  array                          $results Results
	 * @return \mako\database\query\ResultSet
	 */
	protected function createResultSet(array $results): ResultSet
	{
		return new ResultSet($results);
	}

	/**
	 * Executes a SELECT query and returns an array containing all of the result set rows.
	 *
	 * @param  bool                                 $returnResultSet Return result set?
	 * @param  mixed                                ...$fetchMode    Fetch mode
	 * @return array|\mako\database\query\ResultSet
	 */
	protected function fetchAll(bool $returnResultSet, mixed ...$fetchMode): array|ResultSet
	{
		$query = $this->compiler->select();

		$results = $this->connection->all($query['sql'], $query['params'], ...$fetchMode);

		return $returnResultSet ? $this->createResultSet($results) : $results;
	}

	/**
	 * Executes a SELECT query and returns an array containing all of the result set rows.
	 *
	 * @return \mako\database\query\ResultSet
	 */
	public function all(): ResultSet
	{
		return $this->fetchAll(true, PDO::FETCH_CLASS, Result::class);
	}

	/**
	 * Executes a SELECT query and returns the value of the chosen column of the first row of the result set.
	 *
	 * @param  string|null $column The column to select
	 * @return mixed
	 */
	public function column(?string $column = null): mixed
	{
		if($column !== null)
		{
			$this->select([$column]);
		}

		$query = $this->limit(1)->compiler->select();

		return $this->connection->column($query['sql'], $query['params']);
	}

	/**
	 * Executes a SELECT query and returns an array containing the values of the indicated 0-indexed column.
	 *
	 * @param  string|null $column The column to select
	 * @return array
	 */
	public function columns(?string $column = null): array
	{
		if($column !== null)
		{
			$this->select([$column]);
		}

		$query = $this->compiler->select();

		return $this->connection->columns($query['sql'], $query['params']);
	}

	/**
	 * Executes a SELECT query and returns an array where the first column is used as keys and the second as values.
	 *
	 * @param  string $key   The column to use as keys
	 * @param  string $value The column to use as values
	 * @return array
	 */
	public function pairs(string $key, string $value): array
	{
		$this->select([$key, $value]);

		$query = $this->compiler->select();

		return $this->connection->pairs($query['sql'], $query['params']);
	}

	/**
	 * Executes a SELECT query and returns a generator that lets you iterate over the results.
	 *
	 * @param  mixed      ...$fetchMode Fetch mode
	 * @return \Generator
	 */
	protected function fetchYield(mixed ...$fetchMode): Generator
	{
		$query = $this->compiler->select();

		yield from $this->connection->yield($query['sql'], $query['params'], ...$fetchMode);
	}

	/**
	 * Executes a SELECT query and returns a generator that lets you iterate over the results.
	 *
	 * @return \Generator
	 */
	public function yield(): Generator
	{
		yield from $this->fetchYield(PDO::FETCH_CLASS, Result::class);
	}

	/**
	 * Returns the number of records that the query will return.
	 *
	 * @return int
	 */
	protected function paginationCount(): int
	{
		$clone = (clone $this)->clearOrderings();

		if(empty($this->setOperations) && empty($this->groupings) && $this->distinct === false)
		{
			return $clone->count();
		}

		return $this->newInstance()->table(new Subquery(static function (&$query) use ($clone): void
		{
			$query = $clone->inSubqueryContext();
		}, 'count', true))->count();
	}

	/**
	 * Paginates the results using a pagination instance.
	 *
	 * @param  int|null                       $itemsPerPage Number of items per page
	 * @param  array                          $options      Pagination options
	 * @return \mako\database\query\ResultSet
	 */
	public function paginate(?int $itemsPerPage = null, array $options = []): ResultSet
	{
		$count = $this->paginationCount();

		$pagination = static::getPaginationFactory()->create($count, $itemsPerPage, $options);

		if($count > 0)
		{
			$results = $this->limit($pagination->limit())->offset($pagination->offset())->all();
		}
		else
		{
			$results = $this->createResultSet([]);
		}

		$results->setPagination($pagination);

		return $results;
	}

	/**
	 * Fetches data in batches and passes them to the processor closure.
	 *
	 * @param \Closure $processor   Closure that processes the results
	 * @param int      $batchSize   Batch size
	 * @param int      $offsetStart Offset start
	 * @param int|null $offsetEnd   Offset end
	 */
	public function batch(Closure $processor, int $batchSize = 1000, int $offsetStart = 0, ?int $offsetEnd = null): void
	{
		$this->limit($batchSize);

		while(true)
		{
			$query = clone $this;

			if($offsetEnd !== null && $offsetStart >= $offsetEnd)
			{
				break;
			}

			if($offsetStart !== 0)
			{
				$query->offset($offsetStart);
			}

			$results = $query->all();

			if(count($results) > 0)
			{
				$processor($results);

				$offsetStart += $batchSize;
			}
			else
			{
				break;
			}
		}
	}

	/**
	 * Sets the selected column of the query to the chosen aggreate.
	 * Executes the query and returns the result if not in subquery context.
	 *
	 * @param  string                                $function Aggregate function
	 * @param  array|\mako\database\query\Raw|string $column   Column name or array of column names
	 * @return mixed|void
	 */
	protected function aggregate(string $function, array|Raw|string $column)
	{
		$this->select([new Raw(sprintf($function, is_array($column) ? $this->compiler->columns($column) : $this->compiler->column($column)))]);

		if($this->inSubqueryContext === false)
		{
			$query = $this->compiler->select();

			return $this->connection->column($query['sql'], $query['params']);
		}
	}

	/**
	 * Returns the minimum value for the chosen column.
	 *
	 * @param  string $column Column name
	 * @return mixed
	 */
	public function min(string $column): mixed
	{
		return $this->aggregate('MIN(%s)', $column);
	}

	/**
	 * Returns the maximum value for the chosen column.
	 *
	 * @param  string $column Column name
	 * @return mixed
	 */
	public function max(string $column): mixed
	{
		return $this->aggregate('MAX(%s)', $column);
	}

	/**
	 * Returns sum of all the values in the chosen column.
	 *
	 * @param  string $column Column name
	 * @return mixed
	 */
	public function sum(string $column): mixed
	{
		return $this->aggregate('SUM(%s)', $column);
	}

	/**
	 * Returns the average value for the chosen column.
	 *
	 * @param  string $column Column name
	 * @return mixed
	 */
	public function avg(string $column): mixed
	{
		return $this->aggregate('AVG(%s)', $column);
	}

	/**
	 * Returns the number of rows.
	 *
	 * @param  \mako\database\query\Raw|string $column Column name
	 * @return int
	 */
	public function count(Raw|string $column = '*'): int
	{
		return (int) $this->aggregate('COUNT(%s)', $column);
	}

	/**
	 * Returns the number of distinct values of the chosen column.
	 *
	 * @param  array|string $column Column name or array of column names
	 * @return int
	 */
	public function countDistinct(array|string $column): int
	{
		return (int) $this->aggregate('COUNT(DISTINCT %s)', $column);
	}

	/**
	 * Inserts data into the chosen table.
	 *
	 * @param  array $values Associative array of column values
	 * @return bool
	 */
	public function insert(array $values = []): bool
	{
		$query = $this->compiler->insert($values);

		return $this->connection->query($query['sql'], $query['params']);
	}

	/**
	 * Inserts data into the chosen table and returns the auto increment id.
	 *
	 * @param  array     $values     Associative array of column values
	 * @param  string    $primaryKey Primary key
	 * @return false|int
	 */
	public function insertAndGetId(array $values, string $primaryKey = 'id')
	{
		return $this->helper->insertAndGetId($this, $values, $primaryKey);
	}

	/**
	 * Updates data from the chosen table.
	 *
	 * @param  array $values Associative array of column values
	 * @return int
	 */
	public function update(array $values): int
	{
		$query = $this->compiler->update($values);

		return $this->connection->queryAndCount($query['sql'], $query['params']);
	}

	/**
	 * Increments column value.
	 *
	 * @param  string $column    Column name
	 * @param  int    $increment Increment value
	 * @return int
	 */
	public function increment(string $column, int $increment = 1): int
	{
		return $this->update([$column => new Raw("{$this->compiler->escapeIdentifier($column)} + " . (int) $increment)]);
	}

	/**
	 * Decrements column value.
	 *
	 * @param  string $column    Column name
	 * @param  int    $decrement Decrement value
	 * @return int
	 */
	public function decrement(string $column, int $decrement = 1): int
	{
		return $this->update([$column => new Raw("{$this->compiler->escapeIdentifier($column)} - " . (int) $decrement)]);
	}

	/**
	 * Deletes data from the chosen table.
	 *
	 * @return int
	 */
	public function delete(): int
	{
		$query = $this->compiler->delete();

		return $this->connection->queryAndCount($query['sql'], $query['params']);
	}
}
