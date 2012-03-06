<?php

namespace mako\database;

use \PDO;
use \Closure;
use \mako\Database;

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
	*
	*/

	public function where($column, $operator = null, $value = null, $separator = 'AND')
	{
		if($column instanceof Closure)
		{
			$query = new static($this->table, $this->connection);

			call_user_func($column, $query);

			$this->wheres[] = array
			(
				'type'      => 'parenthesizedWhere',
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
	*
	*/

	public function orWhere($column, $operator = null, $value = null)
	{
		return $this->where($column, $operator, $value, 'OR');
	}

	/**
	*
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
	*
	*/

	public function orBetween($column, $value1, $value2)
	{
		return $this->between($column, $value1, $value2, 'OR');
	}

	/**
	*
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
	*
	*/

	public function orIn($column, array $values)
	{
		return $this->in($column, $values, 'OR');
	}

	/**
	*
	*/

	public function notIn($column, array $values)
	{
		return $this->in($column, $values, 'AND', true);
	}

	/**
	*
	*/

	public function orNotIn($column, array $values)
	{
		return $this->in($column, $values, 'OR', true);
	}

	/**
	*
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
	*
	*/

	public function orNull($column)
	{
		return $this->null($column, 'OR');
	}

	/**
	*
	*/

	public function notNull($column)
	{
		return $this->null($column, 'AND', true);
	}

	/**
	*
	*/

	public function orNotNull($column)
	{
		return $this->null($column, 'OR', true);
	}

	/**
	*
	*/

	public function groupBy($column)
	{
		$this->groupings[] = $column;

		return $this;
	}

	/**
	*
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
	*
	*/

	public function orHaving($column, $operator, $value)
	{
		return $this->having($column, $operator, $value, 'OR');
	}

	/**
	*
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
	*
	*/

	public function limit($limit)
	{
		$this->limit = (int) $limit;

		return $this;
	}

	/**
	*
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

		return $this->connection->query($query['sql'], $query['params'], Database::FETCH);
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
	*
	*/

	protected function aggregate($column, $function)
	{
		return $this->column(Database::raw($function . '(' . $this->compiler->wrap($column) . ')'));
	}

	/**
	*
	*/

	public function min($column)
	{
		return $this->aggregate($column, 'MIN');
	}

	/**
	*
	*/

	public function max($column)
	{
		return $this->aggregate($column, 'MAX');
	}

	/**
	*
	*/

	public function sum($column)
	{
		return $this->aggregate($column, 'SUM');
	}

	/**
	*
	*/

	public function avg($column)
	{
		return $this->aggregate($column, 'AVG');
	}

	/**
	*
	*/

	public function count($column = '*')
	{
		return $this->aggregate($column, 'COUNT');
	}

	/**
	*
	*/

	public function insert(array $values)
	{
		$query = $this->compiler->insert($values);

		return $this->connection->query($query['sql'], $query['params']);
	}

	/**
	*
	*/

	public function update(array $values)
	{
		$query = $this->compiler->update($values);

		return $this->connection->query($query['sql'], $query['params']);
	}

	/**
	*
	*/

	public function delete()
	{
		$query = $this->compiler->delete();

		return $this->connection->query($query['sql'], $query['params']);
	}
}

/** -------------------- End of file --------------------**/