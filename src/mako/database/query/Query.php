<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query;

use Closure;
use PDO;

use mako\database\connections\Connection;
use mako\database\query\Join;
use mako\database\query\Raw;
use mako\database\query\Result;
use mako\database\query\ResultSet;
use mako\database\query\Subquery;
use mako\pagination\PaginationFactoryInterface;

/**
 * Query builder.
 *
 * @author Frederic G. Østby
 */
class Query
{
	/**
	 * Database connection.
	 *
	 * @var \mako\database\connections\Connection
	 */
	protected $connection;

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
	 * @var mixed
	 */
	protected $table;

	/**
	 * Select distinct?
	 *
	 * @var bool
	 */
	protected $distinct = false;

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
	 * @var null|int
	 */
	protected $limit = null;

	/**
	 * Offset.
	 *
	 * @var null|int
	 */
	protected $offset = null;

	/**
	 * Lock.
	 *
	 * @var null|bool|string
	 */
	protected $lock = null;

	/**
	 * Prefix.
	 *
	 * @var null|string
	 */
	protected $prefix = null;

	/**
	 * Pagination factory.
	 *
	 * @var \mako\pagination\PaginationFactoryInterface
	 */
	protected static $paginationFactory;

	/**
	 * Constructor.
	 *
	 * @param \mako\database\connections\Connection $connection Database connection
	 */
	public function __construct(Connection $connection)
	{
		$this->connection = $connection;

		$this->helper = $connection->getQueryBuilderHelper();

		$this->compiler = $connection->getQueryCompiler($this);
	}

	/**
	 * Create a fresh compiler instance when we clone the query.
	 */
	public function __clone()
	{
		$this->compiler = $this->connection->getQueryCompiler($this);
	}

	/**
	 * Returns a new query builder instance.
	 *
	 * @return \mako\database\query\Query
	 */
	public function newInstance()
	{
		return new self($this->connection);
	}

	/**
	 * Sets the pagination factory.
	 *
	 * @param \mako\pagination\PaginationFactoryInterface|\Closure $factory Pagination factory
	 */
	public static function setPaginationFactory($factory)
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
	 * @return mixed
	 */
	public function getTable()
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
	 * @return null|int
	 */
	public function getLimit()
	{
		return $this->limit;
	}

	/**
	 * Returns the offset.
	 *
	 * @return null|int
	 */
	public function getOffset()
	{
		return $this->offset;
	}

	/**
	 * Returns the lock.
	 *
	 * @return null|bool|string
	 */
	public function getLock()
	{
		return $this->lock;
	}

	/**
	 * Returns the prefix.
	 *
	 * @return null|string
	 */
	public function getPrefix()
	{
		return $this->prefix;
	}

	/**
	 * Adds a set operation.
	 *
	 * @param  \Closure|\mako\database\query\Query|\mako\database\query\Subquery $query     Query
	 * @param  string                                                            $operation Operation
	 * @return \mako\database\query\Query
	 */
	protected function setOperation($query, string $operation)
	{
		if(($query instanceof Subquery) === false)
		{
			$query = new Subquery($query);
		}

		$this->setOperations[] =
		[
			'query'     => $query,
			'operation' => $operation,
		];

		return $this;
	}

	/**
	 * Adds a UNION operation.
	 *
	 * @param  \Closure|\mako\database\query\Query|\mako\database\query\Subquery $query Query
	 * @return \mako\database\query\Query
	 */
	public function union($query)
	{
		return $this->setOperation($query, 'UNION');
	}

	/**
	 * Adds a UNION ALL operation.
	 *
	 * @param  \Closure|\mako\database\query\Query|\mako\database\query\Subquery $query Query
	 * @return \mako\database\query\Query
	 */
	public function unionAll($query)
	{
		return $this->setOperation($query, 'UNION ALL');
	}

	/**
	 * Adds a INTERSECT operation.
	 *
	 * @param  \Closure|\mako\database\query\Query|\mako\database\query\Subquery $query Query
	 * @return \mako\database\query\Query
	 */
	public function intersect($query)
	{
		return $this->setOperation($query, 'INTERSECT');
	}

	/**
	 * Adds a INTERSECT ALL operation.
	 *
	 * @param  \Closure|\mako\database\query\Query|\mako\database\query\Subquery $query Query
	 * @return \mako\database\query\Query
	 */
	public function intersectAll($query)
	{
		return $this->setOperation($query, 'INTERSECT ALL');
	}

	/**
	 * Adds a EXCEPT operation.
	 *
	 * @param  \Closure|\mako\database\query\Query|\mako\database\query\Subquery $query Query
	 * @return \mako\database\query\Query
	 */
	public function except($query)
	{
		return $this->setOperation($query, 'EXCEPT');
	}

	/**
	 * Adds a EXCEPT ALL operation.
	 *
	 * @param  \Closure|\mako\database\query\Query|\mako\database\query\Subquery $query Query
	 * @return \mako\database\query\Query
	 */
	public function exceptAll($query)
	{
		return $this->setOperation($query, 'EXCEPT ALL');
	}

	/**
	 * Sets table we want to query.
	 *
	 * @param  string|\Closure|\mako\database\query\Subquery|\mako\database\query\Raw $table Database table or subquery
	 * @return \mako\database\query\Query
	 */
	public function table($table)
	{
		if($table instanceof Closure)
		{
			$table = new Subquery($table, 'mako0');
		}

		$this->table = $table;

		return $this;
	}

	/**
	 * Alias of Query::table()
	 *
	 * @param  string|\Closure|\mako\database\query\Subquery|\mako\database\query\Raw $table Database table or subquery
	 * @return \mako\database\query\Query
	 */
	public function from($table)
	{
		return $this->table($table);
	}

	/**
	 * Alias of Query::table()
	 *
	 * @param  string|\Closure|\mako\database\query\Subquery|\mako\database\query\Raw $table Database table or subquery
	 * @return \mako\database\query\Query
	 */
	public function into($table)
	{
		return $this->table($table);
	}

	/**
	 * Sets the columns we want to select.
	 *
	 * @param  array                      $columns Array of columns we want to select from
	 * @return \mako\database\query\Query
	 */
	public function select(array $columns)
	{
		$this->columns = $columns;

		return $this;
	}

	/**
	 * Select distinct?
	 *
	 * @return \mako\database\query\Query
	 */
	public function distinct()
	{
		$this->distinct = true;

		return $this;
	}

	/**
	 * Adds a WHERE clause.
	 *
	 * @param  string|\Closure            $column    Column name or closure
	 * @param  null|string                $operator  Operator
	 * @param  null|mixed                 $value     Value
	 * @param  string                     $separator Clause separator
	 * @return \mako\database\query\Query
	 */
	public function where($column, $operator = null, $value = null, $separator = 'AND')
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
	 * Adds a raw WHERE clause
	 *
	 * @param  string                     $column    Column name or raw SQL
	 * @param  string|null|array          $operator  Operator or parameters
	 * @param  string|null                $raw       Raw SQL
	 * @param  string                     $separator Clause separator
	 * @return \mako\database\query\Query
	 */
	public function whereRaw($column, $operator = null, $raw = null, $separator = 'AND')
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
	 * @param  string|\Closure            $column   Column name or closure
	 * @param  null|string                $operator Operator
	 * @param  null|mixed                 $value    Value
	 * @return \mako\database\query\Query
	 */
	public function orWhere($column, $operator = null, $value = null)
	{
		return $this->where($column, $operator, $value, 'OR');
	}

	/**
	 * Adds a raw OR WHERE clause.
	 *
	 * @param  string                     $column   Column name or raw SQL
	 * @param  string|null|array          $operator Operator or parameters
	 * @param  string|null                $raw      Raw SQL
	 * @return \mako\database\query\Query
	 */
	public function orWhereRaw($column, $operator = null, $raw = null)
	{
		return $this->whereRaw($column, $operator, $raw, 'OR');
	}

	/**
	 * Adds a BETWEEN clause.
	 *
	 * @param  string                     $column    Column name
	 * @param  mixed                      $value1    First value
	 * @param  mixed                      $value2    Second value
	 * @param  string                     $separator Clause separator
	 * @param  bool                       $not       Not between?
	 * @return \mako\database\query\Query
	 */
	public function between($column, $value1, $value2, $separator = 'AND', $not = false)
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
	 * @param  string                     $column Column name
	 * @param  mixed                      $value1 First value
	 * @param  mixed                      $value2 Second value
	 * @return \mako\database\query\Query
	 */
	public function orBetween($column, $value1, $value2)
	{
		return $this->between($column, $value1, $value2, 'OR');
	}

	/**
	 * Adds a NOT BETWEEN clause.
	 *
	 * @param  string                     $column Column name
	 * @param  mixed                      $value1 First value
	 * @param  mixed                      $value2 Second value
	 * @return \mako\database\query\Query
	 */
	public function notBetween($column, $value1, $value2)
	{
		return $this->between($column, $value1, $value2, 'AND', true);
	}

	/**
	 * Adds a OR NOT BETWEEN clause.
	 *
	 * @param  string                     $column Column name
	 * @param  mixed                      $value1 First value
	 * @param  mixed                      $value2 Second value
	 * @return \mako\database\query\Query
	 */
	public function orNotBetween($column, $value1, $value2)
	{
		return $this->between($column, $value1, $value2, 'OR', true);
	}

	/**
	 * Adds a IN clause.
	 *
	 * @param  string                                                                $column    Column name
	 * @param  array|\mako\database\query\Raw|\Closure|\mako\database\query\Subquery $values    Array of values or Subquery
	 * @param  string                                                                $separator Clause separator
	 * @param  bool                                                                  $not       Not in?
	 * @return \mako\database\query\Query
	 */
	public function in($column, $values, $separator = 'AND', $not = false)
	{
		if($values instanceof Raw || $values instanceof Subquery)
		{
			$values = [$values];
		}
		elseif($values instanceof Closure)
		{
			$values = [new Subquery($values)];
		}

		$this->wheres[] =
		[
			'type'      => 'in',
			'column'    => $column,
			'values'    => $values,
			'separator' => $separator,
			'not'       => $not,
		];

		return $this;
	}

	/**
	 * Adds a OR IN clause.
	 *
	 * @param  string                     $column Column name
	 * @param  mixed                      $values Array of values or Subquery
	 * @return \mako\database\query\Query
	 */
	public function orIn($column, $values)
	{
		return $this->in($column, $values, 'OR');
	}

	/**
	 * Adds a NOT IN clause.
	 *
	 * @param  string                     $column Column name
	 * @param  mixed                      $values Array of values or Subquery
	 * @return \mako\database\query\Query
	 */
	public function notIn($column, $values)
	{
		return $this->in($column, $values, 'AND', true);
	}

	/**
	 * Adds a OR NOT IN clause.
	 *
	 * @param  string                     $column Column name
	 * @param  mixed                      $values Array of values or Subquery
	 * @return \mako\database\query\Query
	 */
	public function orNotIn($column, $values)
	{
		return $this->in($column, $values, 'OR', true);
	}

	/**
	 * Adds a IS NULL clause.
	 *
	 * @param  mixed                      $column    Column name
	 * @param  string                     $separator Clause separator
	 * @param  bool                       $not       Not in?
	 * @return \mako\database\query\Query
	 */
	public function isNull($column, $separator = 'AND', $not = false)
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
	 * @param  mixed                      $column Column name
	 * @return \mako\database\query\Query
	 */
	public function orIsNull($column)
	{
		return $this->isNull($column, 'OR');
	}

	/**
	 * Adds a IS NOT NULL clause.
	 *
	 * @param  mixed                      $column Column name
	 * @return \mako\database\query\Query
	 */
	public function isNotNull($column)
	{
		return $this->isNull($column, 'AND', true);
	}

	/**
	 * Adds a OR IS NOT NULL clause.
	 *
	 * @param  mixed                      $column Column name
	 * @return \mako\database\query\Query
	 */
	public function orIsNotNull($column)
	{
		return $this->isNull($column, 'OR', true);
	}

	/**
	 * Adds a EXISTS clause.
	 *
	 * @param  \Closure|\mako\database\query\Subquery $query     Subquery
	 * @param  string                                 $separator Clause separator
	 * @param  bool                                   $not       Not exists?
	 * @return \mako\database\query\Query
	 */
	public function exists($query, $separator = 'AND', $not = false)
	{
		if($query instanceof Closure)
		{
			$query = new Subquery($query);
		}

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
	 * @param  \Closure|\mako\database\query\Subquery $query Subquery
	 * @return \mako\database\query\Query
	 */
	public function orExists($query)
	{
		return $this->exists($query, 'OR');
	}

	/**
	 * Adds a NOT EXISTS clause.
	 *
	 * @param  \Closure|\mako\database\query\Subquery $query Subquery
	 * @return \mako\database\query\Query
	 */
	public function notExists($query)
	{
		return $this->exists($query, 'AND', true);
	}

	/**
	 * Adds a OR NOT EXISTS clause.
	 *
	 * @param  \Closure|\mako\database\query\Subquery $query Subquery
	 * @return \mako\database\query\Query
	 */
	public function orNotExists($query)
	{
		return $this->exists($query, 'OR', true);
	}

	/**
	 * Adds a JOIN clause.
	 *
	 * @param  string                     $table    Table name
	 * @param  string|\Closure            $column1  Column name or closure
	 * @param  string                     $operator Operator
	 * @param  string                     $column2  Column name
	 * @param  string                     $type     Join type
	 * @param  bool                       $raw      Raw join?
	 * @return \mako\database\query\Query
	 */
	public function join($table, $column1 = null, $operator = null, $column2 = null, $type = 'INNER', $raw = false)
	{
		$join = new Join($type, $table);

		if($column1 instanceof Closure)
		{
			$column1($join);
		}
		else
		{
			if($raw)
			{
				$join->onRaw($column1, $operator, $column2);
			}
			else
			{
				$join->on($column1, $operator, $column2);
			}
		}

		$this->joins[] = $join;

		return $this;
	}

	/**
	 * Adds a raw JOIN clause.
	 *
	 * @param  string                     $table    Table name
	 * @param  string                     $column1  Column name or closure
	 * @param  string                     $operator Operator
	 * @param  string                     $raw      Raw SQL
	 * @param  string                     $type     Join type
	 * @return \mako\database\query\Query
	 */
	public function joinRaw($table, $column1, $operator, $raw, $type = 'INNER')
	{
		return $this->join($table, $column1, $operator, $raw, $type, true);
	}

	/**
	 * Adds a LEFT OUTER JOIN clause.
	 *
	 * @param  string                     $table    Table name
	 * @param  string|\Closure            $column1  Column name or closure
	 * @param  string                     $operator Operator
	 * @param  string                     $column2  Column name
	 * @return \mako\database\query\Query
	 */
	public function leftJoin($table, $column1 = null, $operator = null, $column2 = null)
	{
		return $this->join($table, $column1, $operator, $column2, 'LEFT OUTER');
	}

	/**
	 * Adds a raw LEFT OUTER JOIN clause.
	 *
	 * @param  string                     $table    Table name
	 * @param  string                     $column1  Column name or closure
	 * @param  string                     $operator Operator
	 * @param  string                     $raw      Raw SQL
	 * @return \mako\database\query\Query
	 */
	public function leftJoinRaw($table, $column1, $operator, $raw)
	{
		return $this->joinRaw($table, $column1, $operator, $raw, 'LEFT OUTER');
	}

	/**
	 * Adds a GROUP BY clause.
	 *
	 * @param  string|array               $columns Column name or array of column names
	 * @return \mako\database\query\Query
	 */
	public function groupBy($columns)
	{
		$this->groupings = is_array($columns) ? $columns : [$columns];

		return $this;
	}

	/**
	 * Adds a HAVING clause.
	 *
	 * @param  string                     $column    Column name
	 * @param  string                     $operator  Operator
	 * @param  mixed                      $value     Value
	 * @param  string                     $separator Clause separator
	 * @return \mako\database\query\Query
	 */
	public function having($column, $operator, $value, $separator = 'AND')
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
	 * @param  string                     $raw       Raw SQL
	 * @param  string                     $operator  Operator
	 * @param  mixed                      $value     Value
	 * @param  string                     $separator Clause separator
	 * @return \mako\database\query\Query
	 */
	public function havingRaw($raw, $operator, $value, $separator = 'AND')
	{
		return $this->having(new Raw($raw), $operator, $value, $separator);
	}

	/**
	 * Adds a OR HAVING clause.
	 *
	 * @param  string                     $column   Column name
	 * @param  string                     $operator Operator
	 * @param  mixed                      $value    Value
	 * @return \mako\database\query\Query
	 */
	public function orHaving($column, $operator, $value)
	{
		return $this->having($column, $operator, $value, 'OR');
	}

	/**
	 * Adds a raw OR HAVING clause.
	 *
	 * @param  string                     $raw      Raw SQL
	 * @param  string                     $operator Operator
	 * @param  mixed                      $value    Value
	 * @return \mako\database\query\Query
	 */
	public function orHavingRaw($raw, $operator, $value)
	{
		return $this->havingRaw($raw, $operator, $value, 'OR');
	}

	/**
	 * Adds a ORDER BY clause.
	 *
	 * @param  string|array               $columns Column name or array of column names
	 * @param  string                     $order   Sorting order
	 * @return \mako\database\query\Query
	 */
	public function orderBy($columns, $order = 'ASC')
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
	 * @param  string                     $raw   Raw SQL
	 * @param  string                     $order Sorting order
	 * @return \mako\database\query\Query
	 */
	public function orderByRaw($raw, $order = 'ASC')
	{
		return $this->orderBy(new Raw($raw), $order);
	}

	/**
	 * Adds an ascending ORDER BY clause.
	 *
	 * @param  string|array               $columns Column name or array of column names
	 * @return \mako\database\query\Query
	 */
	public function ascending($columns)
	{
		return $this->orderBy($columns, 'ASC');
	}

	/**
	 * Adds a raw ascending ORDER BY clause.
	 *
	 * @param  string                     $raw Raw SQL
	 * @return \mako\database\query\Query
	 */
	public function ascendingRaw($raw)
	{
		return $this->orderByRaw($raw, 'ASC');
	}

	/**
	 * Adds a descending ORDER BY clause.
	 *
	 * @param  string|array               $columns Column name or array of column names
	 * @return \mako\database\query\Query
	 */
	public function descending($columns)
	{
		return $this->orderBy($columns, 'DESC');
	}

	/**
	 * Adds a raw descending ORDER BY clause.
	 *
	 * @param  string                     $raw Raw SQL
	 * @return \mako\database\query\Query
	 */
	public function descendingRaw($raw)
	{
		return $this->orderByRaw($raw, 'DESC');
	}

	/**
	 * Clears the ordering clauses.
	 *
	 * @return \mako\database\query\Query
	 */
	public function clearOrderings()
	{
		$this->orderings = [];

		return $this;
	}

	/**
	 * Adds a LIMIT clause.
	 *
	 * @param  int                        $limit Limit
	 * @return \mako\database\query\Query
	 */
	public function limit($limit)
	{
		$this->limit = (int) $limit;

		return $this;
	}

	/**
	 * Adds a OFFSET clause.
	 *
	 * @param  int                        $offset Offset
	 * @return \mako\database\query\Query
	 */
	public function offset($offset)
	{
		$this->offset = (int) $offset;

		return $this;
	}

	/**
	 * Enable lock.
	 *
	 * @param  bool|string                $lock TRUE for exclusive, FALSE for shared and string for custom
	 * @return \mako\database\query\Query
	 */
	public function lock($lock = true)
	{
		$this->lock = $lock;

		return $this;
	}

	/**
	 * Adds a query prefix.
	 *
	 * @param  string                     $prefix Prefix
	 * @return \mako\database\query\Query
	 */
	public function prefix($prefix)
	{
		$this->prefix = $prefix . ' ';

		return $this;
	}

	/**
	 * Executes a SELECT query and returns the value of the chosen column of the first row of the result set.
	 *
	 * @param  string $column The column to select
	 * @return mixed
	 */
	public function column($column = null)
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
	 * @param  string $column The column to select
	 * @return array
	 */
	public function columns($column = null)
	{
		if($column !== null)
		{
			$this->select([$column]);
		}

		$query = $this->compiler->select();

		return $this->connection->columns($query['sql'], $query['params']);
	}

	/**
	 * Executes a SELECT query and returns the first row of the result set.
	 *
	 * @param   mixed   ...$fetchMode  Fetch mode
	 * @return mixed
	 */
	protected function fetchFirst(...$fetchMode)
	{
		$query = $this->limit(1)->compiler->select();

		return $this->connection->first($query['sql'], $query['params'], ...$fetchMode);
	}

	/**
	 * Executes a SELECT query and returns the first row of the result set.
	 *
	 * @return mixed
	 */
	public function first()
	{
		return $this->fetchFirst(PDO::FETCH_CLASS, Result::class);
	}

	/**
	 * Creates a result set.
	 *
	 * @param  array                          $results Results
	 * @return \mako\database\query\ResultSet
	 */
	protected function createResultSet(array $results)
	{
		return new ResultSet($results);
	}

	/**
	 * Executes a SELECT query and returns an array containing all of the result set rows.
	 *
	 * @param bool $returnResultSet Return result set?
	 * @param   mixed                                 ...$fetchMode     Fetch mode
	 * @return array|\mako\database\query\ResultSet
	 */
	protected function fetchAll($returnResultSet, ...$fetchMode)
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
	public function all()
	{
		return $this->fetchAll(true, PDO::FETCH_CLASS, Result::class);
	}

	/**
	 * Executes a SELECT query and returns a generator that lets you iterate over the results.
	 *
	 * @param   mixed      ...$fetchMode  Fetch mode
	 * @return \Generator
	 */
	protected function fetchYield(...$fetchMode)
	{
		$query = $this->compiler->select();

		yield from $this->connection->yield($query['sql'], $query['params'], ...$fetchMode);
	}

	/**
	 * Executes a SELECT query and returns a generator that lets you iterate over the results.
	 *
	 * @return \Generator
	 */
	public function yield()
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

		if(empty($this->groupings) && $this->distinct === false)
		{
			return $clone->count();
		}

		return $this->newInstance()->table(new Subquery($clone, 'count'))->count();
	}

	/**
	 * Paginates the results using a pagination instance.
	 *
	 * @param  null|int                       $itemsPerPage Number of items per page
	 * @param  array                          $options      Pagination options
	 * @return \mako\database\query\ResultSet
	 */
	public function paginate($itemsPerPage = null, array $options = [])
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
	 * @param int      $offsetEnd   Offset end
	 */
	public function batch(Closure $processor, $batchSize = 1000, $offsetStart = 0, $offsetEnd = null)
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
	 * Executes an aggregate query and returns the result.
	 *
	 * @param  string       $function Aggregate function
	 * @param  string|array $column   Column name or array of column names
	 * @return mixed
	 */
	protected function aggregate($function, $column)
	{
		$aggregate = new Raw(sprintf($function, $this->compiler->columns(is_array($column) ? $column : [$column])));

		$query = $this->select([$aggregate])->compiler->select();

		return $this->connection->column($query['sql'], $query['params']);
	}

	/**
	 * Returns the minimum value for the chosen column.
	 *
	 * @param  string $column Column name
	 * @return int
	 */
	public function min($column)
	{
		return $this->aggregate('MIN(%s)', $column);
	}

	/**
	 * Returns the maximum value for the chosen column.
	 *
	 * @param  string $column Column name
	 * @return int
	 */
	public function max($column)
	{
		return $this->aggregate('MAX(%s)', $column);
	}

	/**
	 * Returns sum of all the values in the chosen column.
	 *
	 * @param  string $column Column name
	 * @return int
	 */
	public function sum($column)
	{
		return $this->aggregate('SUM(%s)', $column);
	}

	/**
	 * Returns the average value for the chosen column.
	 *
	 * @param  string $column Column name
	 * @return float
	 */
	public function avg($column)
	{
		return $this->aggregate('AVG(%s)', $column);
	}

	/**
	 * Returns the number of rows.
	 *
	 * @param  string $column Column name
	 * @return int
	 */
	public function count($column = '*')
	{
		return $this->aggregate('COUNT(%s)', $column);
	}

	/**
	 * Returns the number of distinct values of the chosen column.
	 *
	 * @param  string|array $column Column name or array of column names
	 * @return int
	 */
	public function countDistinct($column)
	{
		return $this->aggregate('COUNT(DISTINCT %s)', $column);
	}

	/**
	 * Inserts data into the chosen table.
	 *
	 * @param  array $values Associative array of column values
	 * @return bool
	 */
	public function insert(array $values = [])
	{
		$query = $this->compiler->insert($values);

		return $this->connection->query($query['sql'], $query['params']);
	}

	/**
	 * Inserts data into the chosen table and returns the auto increment id.
	 *
	 * @param  array    $values     Associative array of column values
	 * @param  string   $primaryKey Primary key
	 * @return int|bool
	 */
	public function insertAndGetId(array $values, $primaryKey = 'id')
	{
		return $this->helper->insertAndGetId($this, $values, $primaryKey);
	}

	/**
	 * Updates data from the chosen table.
	 *
	 * @param  array $values Associative array of column values
	 * @return int
	 */
	public function update(array $values)
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
	public function increment($column, $increment = 1)
	{
		return $this->update([$column => new Raw($this->compiler->escapeIdentifier($column) . ' + ' . (int) $increment)]);
	}

	/**
	 * Decrements column value.
	 *
	 * @param  string $column    Column name
	 * @param  int    $decrement Decrement value
	 * @return int
	 */
	public function decrement($column, $decrement = 1)
	{
		return $this->update([$column => new Raw($this->compiler->escapeIdentifier($column) . ' - ' . (int) $decrement)]);
	}

	/**
	 * Deletes data from the chosen table.
	 *
	 * @return int
	 */
	public function delete()
	{
		$query = $this->compiler->delete();

		return $this->connection->queryAndCount($query['sql'], $query['params']);
	}
}
