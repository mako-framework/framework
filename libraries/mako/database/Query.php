<?php

namespace mako\database;

use \PDO;
use \Closure;
use \mako\Database;
use \mako\database\query\Join;

/**
* Query builder.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Query
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	* Database connection.
	*
	* @var mako\database\Connection
	*/

	public $connection;

	/**
	* Query compiler
	*
	* @var mako\database\query\Compiler
	*/

	public $compiler;

	/**
	* Database table.
	*
	* @var string
	*/

	public $table;

	/**
	* Select distinct?
	*
	* @var boolean
	*/

	public $distinct = false;

	/**
	* Columns from which we are fetching data.
	*
	* @var array
	*/

	public $columns;

	/**
	* WHERE clauses.
	*
	* @var array
	*/

	public $wheres = array();

	/**
	* JOIN clauses.
	*
	* @var array
	*/

	public $joins = array();

	/**
	* GROUP BY clauses.
	*
	* @var array
	*/

	public $groupings = array();

	/**
	* HAVING clauses.
	*
	* @var array
	*/

	public $havings = array();

	/**
	* ORDER BY clauses.
	*
	* @var array
	*/

	public $orderings = array();

	/**
	* Limit.
	*
	* @var int
	*/

	public $limit;

	/**
	* Offset
	*
	* @var int
	*/

	public $offset;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	* Constructor.
	*
	* @access  public
	* @param   string                    Database table
	* @param   mako\database\Conenction  Database connection
	*/

	public function __construct($table, $connection)
	{
		$this->table = $table;

		$this->connection = $connection;

		switch($this->connection->pdo->getAttribute(PDO::ATTR_DRIVER_NAME))
		{
			case 'mysql':
				$this->compiler = new \mako\database\query\compiler\MySQL($this);
			break;
			case 'mssql':
			case 'dblib':
			case 'sqlsrv':
				$this->compiler = new \mako\database\query\compiler\SQLServer($this);
			break;
			case 'oci':
				$this->compiler = new \mako\database\query\compiler\Oracle($this);
			break;
			case 'firebird':
				$this->compiler = new \mako\database\query\compiler\Firebird($this);
			break;
			case 'ibm':
			case 'odbc':
				$this->compiler = new \mako\database\query\compiler\DB2($this);
			break;
			default:
				$this->compiler = new \mako\database\query\Compiler($this);
		}
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	* Select distinct?
	*
	* @return  mako\database\Query
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
	* @param   mixed                Column name or closure
	* @param   string               (optional) Operator
	* @param   mixed                (optional) Value
	* @param   string               (optional) Clause separator
	* @return  mako\database\Query
	*/

	public function where($column, $operator = null, $value = null, $separator = 'AND')
	{
		if($column instanceof Closure)
		{
			$query = new static($this->table, $this->connection);

			call_user_func($column, $query);

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
	* @param   mixed                Column name or closure
	* @param   string               (optional) Operator
	* @param   mixed                (optional) Value
	* @return  mako\database\Query
	*/

	public function orWhere($column, $operator = null, $value = null)
	{
		return $this->where($column, $operator, $value, 'OR');
	}

	/**
	* Adds a BETWEEN clause.
	*
	* @access  public
	* @param   string               Column name
	* @param   mixed                First value
	* @param   mixed                Second value
	* @param   string               (optional) Clause separator
	* @return  mako\database\Query
	*/

	public function between($column, $value1, $value2, $separator = 'AND')
	{
		$this->wheres[] = array
		(
			'type'      => 'between',
			'column'    => $column,
			'value1'    => $value1,
			'value2'    => $value2,
			'separator' => $separator,
		);

		return $this;
	}

	/**
	* Adds a OR BETWEEN clause.
	*
	* @access  public
	* @param   string               Column name
	* @param   mixed                First value
	* @param   mixed                Second value
	* @return  mako\database\Query
	*/

	public function orBetween($column, $value1, $value2)
	{
		return $this->between($column, $value1, $value2, 'OR');
	}

	/**
	* Adds a IN clause.
	*
	* @access  public
	* @param   string               Column name
	* @param   array                Array of values
	* @param   string               (optional) Clause separator
	* @param   boolean              (optional) Not in?
	* @return  mako\database\Query
	*/

	public function in($column, array $values, $separator = 'AND', $not = false)
	{
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
	* @param   mixed                Column name
	* @param   array                Array of values
	* @return  mako\database\Query
	*/

	public function orIn($column, array $values)
	{
		return $this->in($column, $values, 'OR');
	}

	/**
	* Adds a NOT IN clause.
	*
	* @access  public
	* @param   mixed                Column name
	* @param   array                Array of values
	* @return  mako\database\Query
	*/

	public function notIn($column, array $values)
	{
		return $this->in($column, $values, 'AND', true);
	}

	/**
	* Adds a OR NOT IN clause.
	*
	* @access  public
	* @param   mixed                Column name
	* @param   array                Array of values
	* @return  mako\database\Query
	*/

	public function orNotIn($column, array $values)
	{
		return $this->in($column, $values, 'OR', true);
	}

	/**
	* Adds a IS NULL clause.
	*
	* @access  public
	* @param   mixed                Column name
	* @param   string               (optional) Clause separator
	* @param   boolean              (boolean) Not in?
	* @return  mako\database\Query
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
	* @param   mixed                Column name
	* @return  mako\database\Query
	*/

	public function orNull($column)
	{
		return $this->null($column, 'OR');
	}

	/**
	* Adds a IS NOT NULL clause.
	*
	* @access  public
	* @param   mixed                Column name
	* @return  mako\database\Query
	*/

	public function notNull($column)
	{
		return $this->null($column, 'AND', true);
	}

	/**
	* Adds a OR IS NOT NULL clause.
	*
	* @access  public
	* @param   mixed                Column name
	* @return  mako\database\Query
	*/

	public function orNotNull($column)
	{
		return $this->null($column, 'OR', true);
	}

	/**
	* Adds a INNER JOIN clause.
	*
	* @access  public
	* @param   string               Table name
	* @param   mixed                (optional) Column name
	* @param   string               (optional) Operator
	* @param   string               (optional) Column name
	* @param   string               (optional) Join type
	* @return  mako\database\Query
	*/

	public function join($table, $column1 = null, $operator = null, $column2 = null, $type = 'INNER')
	{
		$join = new Join($type, $table);

		if($column1 instanceof Closure)
		{
			call_user_func($column1, $join);
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
	* @param   string               Table name
	* @param   mixed                (optional) Column name
	* @param   string               (optional) Operator
	* @param   string               (optional) Column name
	* @return  mako\database\Query
	*/

	public function leftJoin($table, $column1 = null, $operator = null, $column2 = null)
	{
		return $this->join($table, $column1, $operator, $column2, 'LEFT OUTER');
	}

	/**
	* Adds a GROUP BY clause.
	*
	* @access  public
	* @param   string                Column name
	* @return  mako\database\Query
	*/

	public function groupBy($column)
	{
		$this->groupings[] = $column;

		return $this;
	}

	/**
	* Adds a HAVING clause.
	*
	* @access  public
	* @param   string               Column name
	* @param   string               Operator
	* @param   mixed                Value
	* @param   string               (optional) Clause separator
	* @return  mako\database\Query
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
	* @param   string               Column name
	* @param   string               Operator
	* @param   mixed                Value
	* @return  mako\database\Query
	*/

	public function orHaving($column, $operator, $value)
	{
		return $this->having($column, $operator, $value, 'OR');
	}

	/**
	* Adds a ORDER BY clause.
	*
	* @access  public
	* @param   string               Column name
	* @param   string               Sorint order
	* @return  mako\database\Query
	*/

	public function orderBy($column, $order)
	{
		$this->orderings[] = array
		(
			'column' => $column,
			'order'  => $order,
		);

		return $this;
	}

	/**
	* Adds a LIMIT clause.
	*
	* @access  public
	* @param   int                  Limit
	* @return  mako\database\Query
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
	* @param   int                  Offset
	* @return  mako\database\Query
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
	* @param   array   Column names
	* @return  array
	*/

	public function all(array $columns = array('*'))
	{
		$this->columns = $columns;

		$query = $this->compiler->select();

		return $this->connection->query($query['sql'], $query['params'], Database::FETCH_ALL);
	}

	/**
	* Executes a SELECT query and returns the first row of the result set.
	*
	* @access  public
	* @param   array   Column names
	* @return  mixed
	*/

	public function first(array $columns = array('*'))
	{
		$this->columns = $columns;

		$query = $this->compiler->select();

		return $this->connection->query($query['sql'], $query['params'], Database::FETCH_FIRST);
	}

	/**
	* Executes a SELECT query and returns the value of the chosen column of the first row of the result set.
	*
	* @access  public
	* @param   string   Column name
	* @return  mixed
	*/

	public function column($column)
	{
		$this->columns = array($column);

		$query = $this->compiler->select();

		return $this->connection->query($query['sql'], $query['params'], Database::FETCH_COLUMN);
	}

	/**
	* Executes a aggregate query and returns the result.
	*
	* @access  public
	* @param   string  Column
	* @param   string  Aggregate function
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
	* @param   string  Column name
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
	* @param   string  Column name
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
	* @param   string  Column name
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
	* @param   string  Column name
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
	* @param   string  (optional) Column name
	* @return  float
	*/

	public function count($column = '*')
	{
		return $this->aggregate($column, 'COUNT');
	}

	/**
	* Inserts data into the chosen table.
	*
	* @access  public
	* @param   array    Associative array of column values
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
	* @param   array    Associative array of column values
	* @return  int
	*/

	public function update(array $values)
	{
		$query = $this->compiler->update($values);

		return $this->connection->query($query['sql'], $query['params']);
	}

	/**
	* Icrements column value.
	*
	* @access  public
	* @param   string  Column name
	* @param   int     (optional) Icrement value
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
	* @param   string  Column name
	* @param   int     (optional) Decrement value
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

/** -------------------- End of file --------------------**/