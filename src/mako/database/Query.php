<?php

namespace mako\database;

use \PDO;
use \Closure;
use \mako\Database;
use \mako\database\Connection;
use \mako\database\query\Join;
use \mako\database\query\Subquery;

/**
 * Query builder.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Query
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Database connection.
	 *
	 * @var \mako\database\Connection
	 */

	protected $connection;

	/**
	 * Query compiler
	 *
	 * @var \mako\database\query\Compiler
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
	 * @var boolean
	 */

	protected $distinct = false;

	/**
	 * Columns from which we are fetching data.
	 *
	 * @var array
	 */

	protected $columns = array('*');

	/**
	 * WHERE clauses.
	 *
	 * @var array
	 */

	protected $wheres = array();

	/**
	 * JOIN clauses.
	 *
	 * @var array
	 */

	protected $joins = array();

	/**
	 * GROUP BY clauses.
	 *
	 * @var array
	 */

	protected $groupings = array();

	/**
	 * HAVING clauses.
	 *
	 * @var array
	 */

	protected $havings = array();

	/**
	 * ORDER BY clauses.
	 *
	 * @var array
	 */

	protected $orderings = array();

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

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\database\Conenction  $connection  Database connection
	 * @param   mixed                      $table       Database table or subquery
	 */

	public function __construct(Connection $connection, $table)
	{
		$this->table = $table;

		$this->connection = $connection;

		switch($this->connection->getDriver())
		{
			case 'mysql':
				$this->compiler = new \mako\database\query\compiler\MySQL($this);
			break;
			case 'dblib':
			case 'mssql':
			case 'sqlsrv':
				$this->compiler = new \mako\database\query\compiler\SQLServer($this);
			break;
			case 'oci':
			case 'oracle':
				$this->compiler = new \mako\database\query\compiler\Oracle($this);
			break;
			case 'firebird':
				$this->compiler = new \mako\database\query\compiler\Firebird($this);
			break;
			case 'db2':
			case 'ibm':
			case 'odbc':
				$this->compiler = new \mako\database\query\compiler\DB2($this);
			break;
			case 'nuodb':
				$this->compiler = new \mako\database\query\compiler\NuoDB($this);
			break;
			default:
				$this->compiler = new \mako\database\query\Compiler($this);
		}
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns query compiler instance.
	 * 
	 * @access  public
	 * @return  \mako\database\query\Compiler
	 */

	public function getCompiler()
	{
		return $this->compiler;
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
	 * Sets the columns we want to select.
	 *
	 * @access  public
	 * @param   array                 $columns  Array of columns
	 * @return  \mako\database\Query
	 */

	public function columns(array $columns)
	{
		if(!empty($columns))
		{
			$this->columns = $columns;
		}

		return $this;
	}

	/**
	 * Select distinct?
	 *
	 * @return  \mako\database\Query
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
	 * @param   string|\Closure        $column     Column name or closure
	 * @param   string                 $operator   (optional) Operator
	 * @param   mixed                  $value      (optional) Value
	 * @param   string                 $separator  (optional) Clause separator
	 * @return  \mako\database\Query
	 */

	public function where($column, $operator = null, $value = null, $separator = 'AND')
	{
		if($column instanceof Closure)
		{
			$query = new self($this->connection, $this->table);

			$column($query);

			$this->wheres[] = array
			(
				'type'      => 'nestedWhere',
				'query'     => $query,
				'separator' => $separator,
			);
		}
		else
		{
			$this->wheres[] = array
			(
				'type'      => 'where',
				'column'    => $column,
				'operator'  => $operator,
				'value'     => $value,
				'separator' => $separator,
			);
		}
		
		return $this;
	}

	/**
	 * Adds a OR WHERE clause.
	 *
	 * @access  public
	 * @param   string|\Closure       $column    Column name or closure
	 * @param   string                $operator  (optional) Operator
	 * @param   mixed                 $value     (optional) Value
	 * @return  \mako\database\Query
	 */

	public function orWhere($column, $operator = null, $value = null)
	{
		return $this->where($column, $operator, $value, 'OR');
	}

	/**
	 * Adds a BETWEEN clause.
	 *
	 * @access  public
	 * @param   string                $column     Column name
	 * @param   mixed                 $value1     First value
	 * @param   mixed                 $value2     Second value
	 * @param   string                $separator  (optional) Clause separator
	 * @param   boolean               $not        Not between?
	 * @return  \mako\database\Query
	 */

	public function between($column, $value1, $value2, $separator = 'AND', $not = false)
	{
		$this->wheres[] = array
		(
			'type'      => 'between',
			'column'    => $column,
			'value1'    => $value1,
			'value2'    => $value2,
			'separator' => $separator,
			'not'       => $not,
		);

		return $this;
	}

	/**
	 * Adds a OR BETWEEN clause.
	 *
	 * @access  public
	 * @param   string                $column  Column name
	 * @param   mixed                 $value1  First value
	 * @param   mixed                 $value2  Second value
	 * @return  \mako\database\Query
	 */

	public function orBetween($column, $value1, $value2)
	{
		return $this->between($column, $value1, $value2, 'OR');
	}

	/**
	 * Adds a NOT BETWEEN clause.
	 *
	 * @access  public
	 * @param   string                $column  Column name
	 * @param   mixed                 $value1  First value
	 * @param   mixed                 $value2  Second value
	 * @return  \mako\database\Query
	 */

	public function notBetween($column, $value1, $value2)
	{
		return $this->between($column, $value1, $value2, 'AND', true);
	}

	/**
	 * Adds a OR NOT BETWEEN clause.
	 *
	 * @access  public
	 * @param   string                $column  Column name
	 * @param   mixed                 $value1  First value
	 * @param   mixed                 $value2  Second value
	 * @return  \mako\database\Query
	 */

	public function orNotBetween($column, $value1, $value2)
	{
		return $this->between($column, $value1, $value2, 'OR', true);
	}

	/**
	 * Adds a IN clause.
	 *
	 * @access  public
	 * @param   string                $column     Column name
	 * @param   mixed                 $values     Array of values or Subquery
	 * @param   string                $separator  (optional) Clause separator
	 * @param   boolean               $not        (optional) Not in?
	 * @return  \mako\database\Query
	 */

	public function in($column, $values, $separator = 'AND', $not = false)
	{
		if($values instanceof Subquery)
		{
			$values = array($values);
		}
		
		$this->wheres[] = array
		(
			'type'      => 'in',
			'column'    => $column,
			'values'    => $values,
			'separator' => $separator,
			'not'       => $not,
		);

		return $this;
	}

	/**
	 * Adds a OR IN clause.
	 *
	 * @access  public
	 * @param   string                $column  Column name
	 * @param   mixed                 $values  Array of values or Subquery
	 * @return  \mako\database\Query
	 */

	public function orIn($column, $values)
	{
		return $this->in($column, $values, 'OR');
	}

	/**
	 * Adds a NOT IN clause.
	 *
	 * @access  public
	 * @param   string                $column  Column name
	 * @param   mixed                 $values  Array of values or Subquery
	 * @return  \mako\database\Query
	 */

	public function notIn($column, $values)
	{
		return $this->in($column, $values, 'AND', true);
	}

	/**
	 * Adds a OR NOT IN clause.
	 *
	 * @access  public
	 * @param   string                $column  Column name
	 * @param   mixed                 $values  Array of values or Subquery
	 * @return  \mako\database\Query
	 */

	public function orNotIn($column, $values)
	{
		return $this->in($column, $values, 'OR', true);
	}

	/**
	 * Adds a IS NULL clause.
	 *
	 * @access  public
	 * @param   mixed                 $column     Column name
	 * @param   string                $separator  (optional) Clause separator
	 * @param   boolean               $not        (optional) Not in?
	 * @return  \mako\database\Query
	 */

	public function null($column, $separator = 'AND', $not = false)
	{
		$this->wheres[] = array
		(
			'type'      => 'null',
			'column'    => $column,
			'separator' => $separator,
			'not'       => $not,
		);

		return $this;
	}

	/**
	 * Adds a OR IS NULL clause.
	 *
	 * @access  public
	 * @param   mixed                 $column  Column name
	 * @return  \mako\database\Query
	 */

	public function orNull($column)
	{
		return $this->null($column, 'OR');
	}

	/**
	 * Adds a IS NOT NULL clause.
	 *
	 * @access  public
	 * @param   mixed                 $column  Column name
	 * @return  \mako\database\Query
	 */

	public function notNull($column)
	{
		return $this->null($column, 'AND', true);
	}

	/**
	 * Adds a OR IS NOT NULL clause.
	 *
	 * @access  public
	 * @param   mixed                 $column  Column name
	 * @return  \mako\database\Query
	 */

	public function orNotNull($column)
	{
		return $this->null($column, 'OR', true);
	}

	/**
	 * Adds a EXISTS clause.
	 *
	 * @access  public
	 * @param   \mako\database\query\Subquery  $query      Subquery
	 * @param   string                         $separator  (optional) Clause separator
	 * @param   boolean                        $not        (optional) Not exists?
	 * @return  \mako\database\Query
	 */

	public function exists(Subquery $query, $separator = 'AND', $not = false)
	{
		$this->wheres[] = array
		(
			'type'      => 'exists',
			'query'     => $query,
			'separator' => $separator,
			'not'       => $not,
		);

		return $this;
	}

	/**
	 * Adds a OR EXISTS clause.
	 *
	 * @access  public
	 * @param   \mako\database\query\Subquery  $query  Subquery
	 * @return  \mako\database\Query
	 */

	public function orExists(Subquery $query)
	{
		return $this->exists($query, 'OR');
	}

	/**
	 * Adds a NOT EXISTS clause.
	 *
	 * @access  public
	 * @param   \mako\database\query\Subquery  $query  Subquery
	 * @return  \mako\database\Query
	 */

	public function notExists(Subquery $query)
	{
		return $this->exists($query, 'AND', true);
	}

	/**
	 * Adds a OR NOT EXISTS clause.
	 *
	 * @access  public
	 * @param   \mako\database\query\Subquery  $query  Subquery
	 * @return  \mako\database\Query
	 */

	public function orNotExists(Subquery $query)
	{
		return $this->exists($query, 'or', true);
	}

	/**
	 * Adds a JOIN clause.
	 *
	 * @access  public
	 * @param   string                $table     Table name
	 * @param   string|\Closure        $column1   (optional) Column name or closure
	 * @param   string                $operator  (optional) Operator
	 * @param   string                $column2   (optional) Column name
	 * @param   string                $type      (optional) Join type
	 * @return  \mako\database\Query
	 */

	public function join($table, $column1 = null, $operator = null, $column2 = null, $type = 'INNER')
	{
		$join = new Join($type, $table);

		if($column1 instanceof Closure)
		{
			$column1($join);
		}
		else
		{
			$join->on($column1, $operator, $column2);
		}

		$this->joins[] = $join;

		return $this;
	}

	/**
	 * Adds a LEFT OUTER JOIN clause.
	 *
	 * @access  public
	 * @param   string                $table     Table name
	 * @param   string|\Closure        $column1   (optional) Column name or closure
	 * @param   string                $operator  (optional) Operator
	 * @param   string                $column2   (optional) Column name
	 * @return  \mako\database\Query
	 */

	public function leftJoin($table, $column1 = null, $operator = null, $column2 = null)
	{
		return $this->join($table, $column1, $operator, $column2, 'LEFT OUTER');
	}

	/**
	 * Adds a GROUP BY clause.
	 *
	 * @access  public
	 * @param   string|array          $columns  Column name or array of column names
	 * @return  \mako\database\Query
	 */

	public function groupBy($columns)
	{
		if(!is_array($columns))
		{
			$columns = array($columns);
		}

		$this->groupings = $columns;

		return $this;
	}

	/**
	 * Adds a HAVING clause.
	 *
	 * @access  public
	 * @param   string                $column     Column name
	 * @param   string                $operator   Operator
	 * @param   mixed                 $value      Value
	 * @param   string                $separator  (optional) Clause separator
	 * @return  \mako\database\Query
	 */

	public function having($column, $operator, $value, $separator = 'AND')
	{
		$this->havings[] = array
		(
			'column'    => $column,
			'operator'  => $operator,
			'value'     => $value,
			'separator' => $separator,
		);

		return $this;
	}

	/**
	 * Adds a OR HAVING clause.
	 *
	 * @access  public
	 * @param   string                $column    Column name
	 * @param   string                $operator  Operator
	 * @param   mixed                 $value     Value
	 * @return  \mako\database\Query
	 */

	public function orHaving($column, $operator, $value)
	{
		return $this->having($column, $operator, $value, 'OR');
	}

	/**
	 * Adds a ORDER BY clause.
	 *
	 * @access  public
	 * @param   string|array          $columns  Column name or array of column names
	 * @param   string                $order    (optional) Sorting order
	 * @return  \mako\database\Query
	 */

	public function orderBy($columns, $order = 'ASC')
	{
		if(!is_array($columns))
		{
			$columns = array($columns);
		}

		$this->orderings[] = array
		(
			'column' => $columns,
			'order'  => $order,
		);

		return $this;
	}

	/**
	 * Adds a ascending ORDER BY clause.
	 *
	 * @access  public
	 * @param   string|array          $columns  Column name or array of column names
	 * @return  \mako\database\Query
	 */

	public function ascending($columns)
	{
		return $this->orderBy($columns, 'ASC');
	}

	/**
	 * Adds a descending ORDER BY clause.
	 *
	 * @access  public
	 * @param   string|array          $columns  Column name or array of column names
	 * @return  \mako\database\Query
	 */

	public function descending($columns)
	{
		return $this->orderBy($columns, 'DESC');
	}

	/**
	 * Adds a LIMIT clause.
	 *
	 * @access  public
	 * @param   int                   $limit  Limit
	 * @return  \mako\database\Query
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
	 * @param   int                   $offset  Offset
	 * @return  \mako\database\Query
	 */

	public function offset($offset)
	{
		$this->offset = (int) $offset;

		return $this;
	}

	/**
	 * Executes a SELECT query and returns an array containing all of the result set rows.
	 *
	 * @access  public
	 * @param   array   $columns  (optional) Columns to select
	 * @return  array
	 */

	public function all(array $columns = array())
	{
		$this->columns($columns);

		$query = $this->compiler->select();

		return $this->connection->query($query['sql'], $query['params'], Database::FETCH_ALL);
	}

	/**
	 * Executes a SELECT query and returns the first row of the result set.
	 *
	 * @access  public
	 * @param   array   $columns  (optional) Columns to select
	 * @return  mixed
	 */

	public function first(array $columns = array())
	{
		$this->columns($columns);

		$query = $this->compiler->select();

		return $this->connection->query($query['sql'], $query['params'], Database::FETCH_FIRST);
	}

	/**
	 * Executes a SELECT query and returns the value of the chosen column of the first row of the result set.
	 *
	 * @access  public
	 * @param   string   $column  Column to select
	 * @return  mixed
	 */

	public function column($column)
	{
		$this->columns(array($column));

		$query = $this->compiler->select();

		return $this->connection->query($query['sql'], $query['params'], Database::FETCH_COLUMN);
	}

	/**
	 * Executes a aggregate query and returns the result.
	 *
	 * @access  public
	 * @param   string  $column    Column
	 * @param   string  $function  Aggregate function
	 * @return  mixed
	 */

	protected function aggregate($column, $function)
	{
		return $this->column(Database::raw($function . '(' . $this->compiler->wrap($column) . ')'));
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
	 * @param   string  $column  (optional) Column name
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
		$query = $this->compiler->insert($values);

		return $this->connection->query($query['sql'], $query['params']);
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
		$query = $this->compiler->update($values);

		return $this->connection->query($query['sql'], $query['params']);
	}

	/**
	 * Increments column value.
	 *
	 * @access  public
	 * @param   string  $column     Column name
	 * @param   int     $increment  (optional) Increment value
	 * @return  int
	 */

	public function increment($column, $increment = 1)
	{
		return $this->update(array($column => Database::raw($this->compiler->wrap($column) . ' + ' . (int) $increment)));
	}

	/**
	 * Decrements column value.
	 *
	 * @access  public
	 * @param   string  $column     Column name
	 * @param   int     $decrement  (optional) Decrement value
	 * @return  int
	 */

	public function decrement($column, $decrement = 1)
	{
		return $this->update(array($column => Database::raw($this->compiler->wrap($column) . ' - ' . (int) $decrement)));
	}

	/**
	 * Deletes data from the chosen table.
	 *
	 * @access  public
	 * @return  int
	 */

	public function delete()
	{
		$query = $this->compiler->delete();

		return $this->connection->query($query['sql'], $query['params']);
	}
}

/** -------------------- End of file -------------------- **/