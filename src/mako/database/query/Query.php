<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\query;

use Closure;

use mako\database\Connection;
use mako\database\query\Raw;
use mako\database\query\Join;
use mako\database\query\Subquery;
use mako\database\query\Compiler;
use mako\database\query\compilers\DB2;
use mako\database\query\compilers\Firebird;
use mako\database\query\compilers\MySQL;
use mako\database\query\compilers\NuoDB;
use mako\database\query\compilers\Oracle;
use mako\database\query\compilers\SQLServer;
use mako\pagination\Pagination;

/**
 * Query builder.
 *
 * @author  Frederic G. Østby
 */

class Query
{
	/**
	 * Fetch mode.
	 *
	 * @var null
	 */

	const FETCH_MODE = null;

	/**
	 * Database connection.
	 *
	 * @var \mako\database\Connection
	 */

	protected $connection;

	/**
	 * Database table.
	 *
	 * @var mixed
	 */

	protected $table;

	/**
	 * Select distinct?
	 *
	 * @var boolean
	 */

	protected $distinct = false;

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
	 * @var int
	 */

	protected $limit = null;

	/**
	 * Offset
	 *
	 * @var int
	 */

	protected $offset = null;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\database\Conenction  $connection  Database connection
	 */

	public function __construct(Connection $connection)
	{
		$this->connection = $connection;
	}

	/**
	 * Returns query compiler instance.
	 *
	 * @access  public
	 * @return  \mako\database\query\Compiler
	 */

	public function getCompiler()
	{
		switch($this->connection->getDialect())
		{
			case 'mysql':
				return new MySQL($this);
				break;
			case 'dblib':
			case 'mssql':
			case 'sqlsrv':
				return new SQLServer($this);
				break;
			case 'oci':
			case 'oracle':
				return new Oracle($this);
				break;
			case 'firebird':
				return new Firebird($this);
				break;
			case 'db2':
			case 'ibm':
			case 'odbc':
				return new DB2($this);
				break;
			case 'nuodb':
				return new NuoDB($this);
				break;
			default:
				return new Compiler($this);
		}
	}

	/**
	 * Returns the database table.
	 *
	 * @access  public
	 * @return  mixed
	 */

	public function getTable()
	{
		return $this->table;
	}

	/**
	 * Is it a distict select?
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function isDistinct()
	{
		return $this->distinct;
	}

	/**
	 * Returns the columns from which we are fetching data.
	 *
	 * @access  public
	 * @return  array
	 */

	public function getColumns()
	{
		return $this->columns;
	}

	/**
	 * Returns WHERE clauses.
	 *
	 * @access  public
	 * @return  array
	 */

	public function getWheres()
	{
		return $this->wheres;
	}

	/**
	 * Returns JOIN clauses.
	 *
	 * @access  public
	 * @return  array
	 */

	public function getJoins()
	{
		return $this->joins;
	}

	/**
	 * Returns GROUP BY clauses.
	 *
	 * @access  public
	 * @return  array
	 */

	public function getGroupings()
	{
		return $this->groupings;
	}

	/**
	 * Returns HAVING clauses.
	 *
	 * @access  public
	 * @return  array
	 */

	public function getHavings()
	{
		return $this->havings;
	}

	/**
	 * Returns ORDER BY clauses.
	 *
	 * @access  public
	 * @return  array
	 */

	public function getOrderings()
	{
		return $this->orderings;
	}

	/**
	 * Returns the limit.
	 *
	 * @access  public
	 * @return  int
	 */

	public function getLimit()
	{
		return $this->limit;
	}

	/**
	 * Returns the offset.
	 *
	 * @access  public
	 * @return  int
	 */

	public function getOffset()
	{
		return $this->offset;
	}

	/**
	 * Sets table we want to query.
	 *
	 * @access  public
	 * @param   string|\Closure|\mako\database\query\Subquery|\mako\database\query\Raw  $table  Database table or subquery
	 * @return  \mako\database\query\Query
	 */

	public function table($table)
	{
		if($table instanceof Closure)
		{
			$subquery = new self($this->connection);

			$table($subquery);

			$table = new Subquery($subquery, 'mako0');
		}

		$this->table = $table;

		return $this;
	}

	/**
	 * Alias of Query::table()
	 *
	 * @access  public
	 * @param   string|\Closure|\mako\database\query\Subquery|\mako\database\query\Raw  $table  Database table or subquery
	 * @return  \mako\database\query\Query
	 */

	public function from($table)
	{
		return $this->table($table);
	}

	/**
	 * Alias of Query::table()
	 *
	 * @access  public
	 * @param   string|\Closure|\mako\database\query\Subquery|\mako\database\query\Raw  $table  Database table or subquery
	 * @return  \mako\database\query\Query
	 */

	public function into($table)
	{
		return $this->table($table);
	}

	/**
	 * Sets the columns we want to select.
	 *
	 * @access  public
	 * @param   array                       $columns  Array of columns we want to select from
	 * @return  \mako\database\query\Query
	 */

	public function select(array $columns)
	{
		$this->columns = $columns;

		return $this;
	}

	/**
	 * Select distinct?
	 *
	 * @return  \mako\database\query\Query
	 */

	public function distinct()
	{
		$this->distinct = true;

		return $this;
	}

	/**
	 * Adds a WHERE clause.
	 *
	 * @access  public
	 * @param   string|\Closure             $column     Column name or closure
	 * @param   null|string                 $operator   Operator
	 * @param   null|mixed                  $value      Value
	 * @param   string                      $separator  Clause separator
	 * @return  \mako\database\query\Query
	 */

	public function where($column, $operator = null, $value = null, $separator = 'AND')
	{
		if($column instanceof Closure)
		{
			$query = new self($this->connection);

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
	 * @access  public
	 * @param   string                      $column     Column name or closure
	 * @param   string                      $operator   Operator
	 * @param   string                      $raw        Raw SQL
	 * @param   string                      $separator  Clause separator
	 * @return  \mako\database\query\Query
	 */

	public function whereRaw($column, $operator, $raw, $separator = 'AND')
	{
		return $this->where($column, $operator, new Raw($raw), $separator);
	}

	/**
	 * Adds a OR WHERE clause.
	 *
	 * @access  public
	 * @param   string|\Closure             $column    Column name or closure
	 * @param   null|string                 $operator  Operator
	 * @param   null|mixed                  $value     Value
	 * @return  \mako\database\query\Query
	 */

	public function orWhere($column, $operator = null, $value = null)
	{
		return $this->where($column, $operator, $value, 'OR');
	}

	/**
	 * Adds a raw OR WHERE clause.
	 *
	 * @access  public
	 * @param   string                      $column    Column name or closure
	 * @param   string                      $operator  Operator
	 * @param   string                      $raw       Raw SQL
	 * @return  \mako\database\query\Query
	 */

	public function orWhereRaw($column, $operator, $raw)
	{
		return $this->whereRaw($column, $operator, $raw, 'OR');
	}

	/**
	 * Adds a BETWEEN clause.
	 *
	 * @access  public
	 * @param   string                      $column     Column name
	 * @param   mixed                       $value1     First value
	 * @param   mixed                       $value2     Second value
	 * @param   string                      $separator  Clause separator
	 * @param   boolean                     $not        Not between?
	 * @return  \mako\database\query\Query
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
	 * @access  public
	 * @param   string                      $column  Column name
	 * @param   mixed                       $value1  First value
	 * @param   mixed                       $value2  Second value
	 * @return  \mako\database\query\Query
	 */

	public function orBetween($column, $value1, $value2)
	{
		return $this->between($column, $value1, $value2, 'OR');
	}

	/**
	 * Adds a NOT BETWEEN clause.
	 *
	 * @access  public
	 * @param   string                      $column  Column name
	 * @param   mixed                       $value1  First value
	 * @param   mixed                       $value2  Second value
	 * @return  \mako\database\query\Query
	 */

	public function notBetween($column, $value1, $value2)
	{
		return $this->between($column, $value1, $value2, 'AND', true);
	}

	/**
	 * Adds a OR NOT BETWEEN clause.
	 *
	 * @access  public
	 * @param   string                      $column  Column name
	 * @param   mixed                       $value1  First value
	 * @param   mixed                       $value2  Second value
	 * @return  \mako\database\query\Query
	 */

	public function orNotBetween($column, $value1, $value2)
	{
		return $this->between($column, $value1, $value2, 'OR', true);
	}

	/**
	 * Adds a IN clause.
	 *
	 * @access  public
	 * @param   string                                            $column     Column name
	 * @param   array|Raw|\Closure|\mako\database\query\Subquery  $values     Array of values or Subquery
	 * @param   string                                            $separator  Clause separator
	 * @param   boolean                                           $not        Not in?
	 * @return  \mako\database\query\Query
	 */

	public function in($column, $values, $separator = 'AND', $not = false)
	{
		if($values instanceof Raw || $values instanceof Subquery)
		{
			$values = [$values];
		}
		elseif($values instanceof Closure)
		{
			$subquery = new self($this->connection);

			$values($subquery);

			$values = [new Subquery($subquery)];
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
	 * @access  public
	 * @param   string                      $column  Column name
	 * @param   mixed                       $values  Array of values or Subquery
	 * @return  \mako\database\query\Query
	 */

	public function orIn($column, $values)
	{
		return $this->in($column, $values, 'OR');
	}

	/**
	 * Adds a NOT IN clause.
	 *
	 * @access  public
	 * @param   string                      $column  Column name
	 * @param   mixed                       $values  Array of values or Subquery
	 * @return  \mako\database\query\Query
	 */

	public function notIn($column, $values)
	{
		return $this->in($column, $values, 'AND', true);
	}

	/**
	 * Adds a OR NOT IN clause.
	 *
	 * @access  public
	 * @param   string                      $column  Column name
	 * @param   mixed                       $values  Array of values or Subquery
	 * @return  \mako\database\query\Query
	 */

	public function orNotIn($column, $values)
	{
		return $this->in($column, $values, 'OR', true);
	}

	/**
	 * Adds a IS NULL clause.
	 *
	 * @access  public
	 * @param   mixed                       $column     Column name
	 * @param   string                      $separator  Clause separator
	 * @param   boolean                     $not        Not in?
	 * @return  \mako\database\query\Query
	 */

	public function null($column, $separator = 'AND', $not = false)
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
	 * @access  public
	 * @param   mixed                       $column  Column name
	 * @return  \mako\database\query\Query
	 */

	public function orNull($column)
	{
		return $this->null($column, 'OR');
	}

	/**
	 * Adds a IS NOT NULL clause.
	 *
	 * @access  public
	 * @param   mixed                       $column  Column name
	 * @return  \mako\database\query\Query
	 */

	public function notNull($column)
	{
		return $this->null($column, 'AND', true);
	}

	/**
	 * Adds a OR IS NOT NULL clause.
	 *
	 * @access  public
	 * @param   mixed                        $column  Column name
	 * @return  \mako\database\query\Query
	 */

	public function orNotNull($column)
	{
		return $this->null($column, 'OR', true);
	}

	/**
	 * Adds a EXISTS clause.
	 *
	 * @access  public
	 * @param   \Closure|\mako\database\query\Subquery  $query      Subquery
	 * @param   string                                  $separator  Clause separator
	 * @param   boolean                                 $not        Not exists?
	 * @return  \mako\database\query\Query
	 */

	public function exists($query, $separator = 'AND', $not = false)
	{
		if($query instanceof Closure)
		{
			$subquery = new self($this->connection);

			$query($subquery);

			$query = new Subquery($subquery);
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
	 * @access  public
	 * @param   \Closure|\mako\database\query\Subquery  $query  Subquery
	 * @return  \mako\database\query\Query
	 */

	public function orExists($query)
	{
		return $this->exists($query, 'OR');
	}

	/**
	 * Adds a NOT EXISTS clause.
	 *
	 * @access  public
	 * @param   \Closure|\mako\database\query\Subquery  $query  Subquery
	 * @return  \mako\database\query\Query
	 */

	public function notExists($query)
	{
		return $this->exists($query, 'AND', true);
	}

	/**
	 * Adds a OR NOT EXISTS clause.
	 *
	 * @access  public
	 * @param   \Closure|\mako\database\query\Subquery  $query  Subquery
	 * @return  \mako\database\query\Query
	 */

	public function orNotExists($query)
	{
		return $this->exists($query, 'OR', true);
	}

	/**
	 * Adds a JOIN clause.
	 *
	 * @access  public
	 * @param   string                      $table     Table name
	 * @param   string|\Closure             $column1   Column name or closure
	 * @param   string                      $operator  Operator
	 * @param   string                      $column2   Column name
	 * @param   string                      $type      Join type
	 * @param   boolean                     $raw       Raw join?
	 * @return  \mako\database\query\Query
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
	 * @access  public
	 * @param   string                      $table     Table name
	 * @param   string                      $column1   Column name or closure
	 * @param   string                      $operator  Operator
	 * @param   string                      $raw       Raw SQL
	 * @param   string                      $type      Join type
	 * @return  \mako\database\query\Query
	 */

	public function joinRaw($table, $column1, $operator, $raw, $type = 'INNER')
	{
		return $this->join($table, $column1, $operator, $raw, $type, true);
	}

	/**
	 * Adds a LEFT OUTER JOIN clause.
	 *
	 * @access  public
	 * @param   string                      $table     Table name
	 * @param   string|\Closure             $column1   Column name or closure
	 * @param   string                      $operator  Operator
	 * @param   string                      $column2   Column name
	 * @return  \mako\database\query\Query
	 */

	public function leftJoin($table, $column1 = null, $operator = null, $column2 = null)
	{
		return $this->join($table, $column1, $operator, $column2, 'LEFT OUTER');
	}

	/**
	 * Adds a raw LEFT OUTER JOIN clause.
	 *
	 * @access  public
	 * @param   string                      $table     Table name
	 * @param   string                      $column1   Column name or closure
	 * @param   string                      $operator  Operator
	 * @param   string                      $raw       Raw SQL
	 * @return  \mako\database\query\Query
	 */

	public function leftJoinRaw($table, $column1, $operator, $raw)
	{
		return $this->joinRaw($table, $column1, $operator, $raw, 'LEFT OUTER');
	}

	/**
	 * Adds a GROUP BY clause.
	 *
	 * @access  public
	 * @param   string|array                $columns  Column name or array of column names
	 * @return  \mako\database\query\Query
	 */

	public function groupBy($columns)
	{
		if(!is_array($columns))
		{
			$columns = [$columns];
		}

		$this->groupings = $columns;

		return $this;
	}

	/**
	 * Adds a HAVING clause.
	 *
	 * @access  public
	 * @param   string                      $column     Column name
	 * @param   string                      $operator   Operator
	 * @param   mixed                       $value      Value
	 * @param   string                      $separator  Clause separator
	 * @return  \mako\database\query\Query
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
	 * Adds a OR HAVING clause.
	 *
	 * @access  public
	 * @param   string                      $column    Column name
	 * @param   string                      $operator  Operator
	 * @param   mixed                       $value     Value
	 * @return  \mako\database\query\Query
	 */

	public function orHaving($column, $operator, $value)
	{
		return $this->having($column, $operator, $value, 'OR');
	}

	/**
	 * Adds a ORDER BY clause.
	 *
	 * @access  public
	 * @param   string|array                $columns  Column name or array of column names
	 * @param   string                      $order    Sorting order
	 * @return  \mako\database\query\Query
	 */

	public function orderBy($columns, $order = 'ASC')
	{
		if(!is_array($columns))
		{
			$columns = [$columns];
		}

		$this->orderings[] =
		[
			'column' => $columns,
			'order'  => ($order === 'ASC' || $order === 'asc') ? 'ASC' : 'DESC',
		];

		return $this;
	}

	/**
	 * Adds a raw ORDER BY clause.
	 *
	 * @access  public
	 * @param   string                      $raw    Raw SQL
	 * @param   string                      $order  Sorting order
	 * @return  \mako\database\query\Query
	 */

	public function orderByRaw($raw, $order = 'ASC')
	{
		return $this->orderBy(new Raw($raw), $order);
	}

	/**
	 * Adds an ascending ORDER BY clause.
	 *
	 * @access  public
	 * @param   string|array                $columns  Column name or array of column names
	 * @return  \mako\database\query\Query
	 */

	public function ascending($columns)
	{
		return $this->orderBy($columns, 'ASC');
	}

	/**
	 * Adds a raw ascending ORDER BY clause.
	 *
	 * @access  public
	 * @param   string                     $raw  Raw SQL
	 * @return  \mako\database\query\Query
	 */

	public function ascendingRaw($raw)
	{
		return $this->orderByRaw($raw, 'ASC');
	}

	/**
	 * Adds a descending ORDER BY clause.
	 *
	 * @access  public
	 * @param   string|array                $columns  Column name or array of column names
	 * @return  \mako\database\query\Query
	 */

	public function descending($columns)
	{
		return $this->orderBy($columns, 'DESC');
	}

	/**
	 * Adds a raw descending ORDER BY clause.
	 *
	 * @access  public
	 * @param   string                      $raw  Raw SQL
	 * @return  \mako\database\query\Query
	 */

	public function descendingRaw($raw)
	{
		return $this->orderByRaw($raw, 'DESC');
	}

	/**
	 * Adds a LIMIT clause.
	 *
	 * @access  public
	 * @param   int                         $limit  Limit
	 * @return  \mako\database\query\Query
	 */

	public function limit($limit)
	{
		$this->limit = (int) $limit;

		return $this;
	}

	/**
	 * Adds a OFFSET clause.
	 *
	 * @access  public
	 * @param   int                         $offset  Offset
	 * @return  \mako\database\query\Query
	 */

	public function offset($offset)
	{
		$this->offset = (int) $offset;

		return $this;
	}

	/**
     * Paginates the results using a paniation instance.
     *
     * @access  public
     * @param   \mako\pagination\Pagination  $pagination  Pagination instance
     * @return  \mako\database\query\Query
     */

	public function paginate(Pagination $pagination)
	{
		return $this->limit($pagination->limit())->offset($pagination->offset());
	}

	/**
	 * Executes a SELECT query and returns the value of the chosen column of the first row of the result set.
	 *
	 * @access  public
	 * @param   string  $column  The column to select
	 * @return  mixed
	 */

	public function column($column = null)
	{
		if($column !== null)
		{
			$this->select([$column]);
		}

		$query = $this->limit(1)->getCompiler()->select();

		return $this->connection->column($query['sql'], $query['params']);
	}

	/**
	 * Executes a SELECT query and returns the first row of the result set.
	 *
	 * @access  public
	 * @return  mixed
	 */

	public function first()
	{
		$query = $this->limit(1)->getCompiler()->select();

		return $this->connection->first($query['sql'], $query['params'], static::FETCH_MODE);
	}

	/**
	 * Executes a SELECT query and returns an array containing all of the result set rows.
	 *
	 * @access  public
	 * @return  array
	 */

	public function all()
	{
		$query = $this->getCompiler()->select();

		return $this->connection->all($query['sql'], $query['params'], static::FETCH_MODE);
	}

	/**
	 * Fetches data in batches and passes them to the processor closure.
	 *
	 * @access  public
	 * @param   \Closure  $processor    Closure that processes the results
	 * @param   int       $batchSize    Batch size
	 * @param   int       $offsetStart  Offset start
	 * @param   int       $offsetEnd    Offset end
	 */

	public function batch(Closure $processor, $batchSize = 1000, $offsetStart = 0, $offsetEnd = null)
	{
		$this->limit($batchSize);

		while(true)
		{
			if($offsetEnd !== null && $offsetStart >= $offsetEnd)
			{
				break;
			}

			if($offsetStart !== 0)
			{
				$this->offset($offsetStart);
			}

			$results = $this->all();

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
	 * @access  public
	 * @param   string  $column    Column
	 * @param   string  $function  Aggregate function
	 * @return  mixed
	 */

	protected function aggregate($column, $function)
	{
		$aggregate = new Raw($function . '(' . $this->getCompiler()->escapeTableAndOrColumn($column) . ')');

		$query = $this->select([$aggregate])->getCompiler()->select();

		return $this->connection->column($query['sql'], $query['params']);
	}

	/**
	 * Returns the minimum value for the chosen column.
	 *
	 * @access  public
	 * @param   string  $column  Column name
	 * @return  int
	 */

	public function min($column)
	{
		return $this->aggregate($column, 'MIN');
	}

	/**
	 * Returns the maximum value for the chosen column.
	 *
	 * @access  public
	 * @param   string  $column  Column name
	 * @return  int
	 */

	public function max($column)
	{
		return $this->aggregate($column, 'MAX');
	}

	/**
	 * Returns sum of all the values in the chosen column.
	 *
	 * @access  public
	 * @param   string  $column  Column name
	 * @return  int
	 */

	public function sum($column)
	{
		return $this->aggregate($column, 'SUM');
	}

	/**
	 * Returns the average value for the chosen column.
	 *
	 * @access  public
	 * @param   string  $column  Column name
	 * @return  float
	 */

	public function avg($column)
	{
		return $this->aggregate($column, 'AVG');
	}

	/**
	 * Returns the number of rows.
	 *
	 * @access  public
	 * @param   string  $column  Column name
	 * @return  int
	 */

	public function count($column = '*')
	{
		return $this->aggregate($column, 'COUNT');
	}

	/**
	 * Inserts data into the chosen table.
	 *
	 * @access  public
	 * @param   array    $values  Associative array of column values
	 * @return  boolean
	 */

	public function insert(array $values)
	{
		$query = $this->getCompiler()->insert($values);

		return $this->connection->query($query['sql'], $query['params']);
	}

	/**
	 * Inserts data into the chosen table and returns the auto increment id.
	 *
	 * @access  public
	 * @param   array        $values      Associative array of column values
	 * @param   string       $primaryKey  Primary key
	 * @return  int|boolean
	 */

	public function insertAndGetId(array $values, $primaryKey = 'id')
	{
		if($this->insert($values) === false)
		{
			return false;
		}

		switch($this->connection->getDriver())
		{
			case 'pgsql':
				$sequence = $this->table . '_' . $primaryKey . '_seq';
				break;
			default:
				$sequence = null;
		}

		return $this->connection->getPDO()->lastInsertId($sequence);
	}

	/**
	 * Updates data from the chosen table.
	 *
	 * @access  public
	 * @param   array    $values  Associative array of column values
	 * @return  int
	 */

	public function update(array $values)
	{
		$query = $this->getCompiler()->update($values);

		return $this->connection->queryAndCount($query['sql'], $query['params']);
	}

	/**
	 * Increments column value.
	 *
	 * @access  public
	 * @param   string  $column     Column name
	 * @param   int     $increment  Increment value
	 * @return  int
	 */

	public function increment($column, $increment = 1)
	{
		return $this->update([$column => new Raw($this->getCompiler()->escapeIdentifier($column) . ' + ' . (int) $increment)]);
	}

	/**
	 * Decrements column value.
	 *
	 * @access  public
	 * @param   string  $column     Column name
	 * @param   int     $decrement  Decrement value
	 * @return  int
	 */

	public function decrement($column, $decrement = 1)
	{
		return $this->update([$column => new Raw($this->getCompiler()->escapeIdentifier($column) . ' - ' . (int) $decrement)]);
	}

	/**
	 * Deletes data from the chosen table.
	 *
	 * @access  public
	 * @return  int
	 */

	public function delete()
	{
		$query = $this->getCompiler()->delete();

		return $this->connection->queryAndCount($query['sql'], $query['params']);
	}
}