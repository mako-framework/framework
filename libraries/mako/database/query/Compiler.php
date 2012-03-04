<?php

namespace mako\database\query;

use \mako\database\query\Expression;

/**
* Compiles SQL queries.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Compiler
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	* Wrapper used to escape table and column names.
	*
	* @var string
	*/

	protected $wrapper = '"%s"';

	/**
	*
	*/

	protected $query;

	/**
	* Query parameters.
	*
	* @var array
	*/

	public $params = array();

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	* Constructor.
	*
	* @access  public
	* @param   mako\database\Query  Query builder
	*/

	public function __construct($query)
	{
		$this->query = $query;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	* Wraps table and column names with dialect specific escape characters.
	*
	* @access  public
	* @param   mixed   Value to wrap
	* @return  string
	*/

	protected function wrap($value)
	{
		if($value === '*')
		{
			return $value;
		}
		elseif($value instanceof Expression)
		{
			return $value->get();
		}
		else
		{
			if(strpos(strtolower($value), ' as ') !== false)
			{
				$values = explode(' ', $value);

				return sprintf('%s AS %s', $this->wrap($values[0]), $this->wrap($values[2]));
			}

			foreach(explode('.', $value) as $segment)
			{
				if($segment == '*')
				{
					$wrapped[] = $segment;
				}
				else
				{
					$wrapped[] = sprintf($this->wrapper, $segment);
				}
			}

			return implode('.', $wrapped);
		}
	}

	/**
	*
	*/

	protected function columns($columns)
	{
		return implode(', ', array_map(array($this, 'wrap'), $columns));
	}

	/**
	*
	*/

	protected function param($param)
	{
		return ($param instanceof Expression) ? $param->get() : '?';
	}

	/**
	*
	*/

	protected function params($params)
	{
		return implode(', ', array_map(array($this, 'param'), $params));
	}

	/**
	*
	*/

	protected function where($where)
	{
		$this->params[] = $where['value'];

		return $this->wrap($where['column']) . ' ' . $where['operator'] . ' ' . $this->param($where['value']);
	}

	/**
	*
	*/

	protected function between($where)
	{
		$this->params[] = $where['value1'];
		$this->params[] = $where['value2'];

		return $this->wrap($where['column']) . ' BETWEEN ' . $this->param($where['value1']) . ' AND ' . $this->param($where['value2']);
	}

	/**
	*
	*/

	protected function in($where)
	{
		$this->params = array_merge($this->params, $where['values']);

		return $this->wrap($where['column']) . ($where['not'] ? ' NOT IN ' : ' IN ') . '(' . $this->params($where['values']) . ')';
	}

	/**
	*
	*/

	public function null($where)
	{
		return $this->wrap($where['column']) . ($where['not'] ? ' IS NOT NULL' : ' IS NULL');
	}

	/**
	* Compiles WHERE clauses.
	*
	* @access  protected
	* @return  string
	*/

	protected function wheres()
	{
		if(empty($this->query->wheres))
		{
			return '';
		}

		$sql = array();

		foreach($this->query->wheres as $where)
		{
			$sql[] = $where['separator'] . ' ' . $this->$where['type']($where);
		}

		return ' WHERE ' . preg_replace('/AND |OR /', '', implode(' ', $sql), 1);
	}

	/**
	* Compiles GROUP BY clauses.
	*
	* @access  protected
	* @return  string
	*/

	protected function groupings()
	{
		return empty($this->query->groupings) ? '' : ' GROUP BY ' . $this->columns($this->query->groupings);
	}

	/**
	* Compiles ORDER BY clauses.
	*
	* @access  protected
	* @return  string
	*/

	protected function orderings()
	{
		if(empty($this->query->orderings))
		{
			return '';
		}

		$sql = array();

		foreach($this->query->orderings as $order)
		{
			$sql[] = $this->wrap($order['column']) . ' ' . strtoupper($order['order']);
		}

		return ' ORDER BY ' . implode(', ', $sql);
	}

	/**
	*
	*/

	protected function having($having)
	{
		$this->params[] = $having['value'];

		$sql  = ($having['column'] instanceof Expression) ? $having['column']->get() : $this->wrap($having['column']);
		$sql .= ' ' . $having['operator'] . ' ' . $this->param($having['value']);

		return $sql;
	}

	/**
	* Compiles HAVING clauses.
	*
	* @access  protected
	* @return  string
	*/

	protected function havings()
	{
		if(empty($this->query->havings))
		{
			return '';
		}

		$sql = array();

		foreach($this->query->havings as $having)
		{
			$sql[] = $having['separator'] . ' ' . $this->having($having); 
		}

		return ' HAVING ' . preg_replace('/AND |OR /', '', implode(' ', $sql), 1);
	}

	/**
	* Compiles LIMIT clauses.
	*
	* @access  protected
	* @return  string
	*/

	protected function limit()
	{
		return empty($this->query->limit) ? '' : ' LIMIT ' . $this->query->limit;
	}

	/**
	* Compiles OFFSET clause.
	*
	* @access  protected
	* @return  string
	*/

	protected function offset()
	{
		return empty($this->query->offset) ? '' : ' OFFSET ' . $this->query->offset;
	}	

	/**
	* Compiles a SELECT query.
	*
	* @access  public
	* @return  string
	*/

	public function select()
	{
		$sql  = $this->query->distinct ? 'SELECT DISTINCT ' : 'SELECT ';
		$sql .= $this->columns($this->query->columns);
		$sql .= ' FROM ';
		$sql .= $this->wrap($this->query->table);
		$sql .= $this->wheres();
		$sql .= $this->groupings();
		$sql .= $this->orderings();
		$sql .= $this->havings();
		$sql .= $this->limit();
		$sql .= $this->offset();

		return array('sql' => $sql, 'params' => $this->params);
	}

	/**
	* Compiles a INSERT query.
	*
	* @access  public
	* @return  string
	*/

	public function insert($values)
	{
		$sql  = 'INSERT INTO ';
		$sql .= $this->wrap($this->query->table);
		$sql .= ' (' . $this->columns(array_keys($values)) . ') ';
		$sql .= 'VALUES';
		$sql .= ' (' . $this->params($values) . ')';

		return array('sql' => $sql, 'params' => array_values($values));
	}

	/**
	* Compiles a UPDATE query.
	*
	* @access  public
	* @return  string
	*/

	public function update($values)
	{
		$columns = array();

		foreach($values as $column => $value)
		{
			$columns[] .= $this->wrap($column) . ' = ' . $this->param($value);
		}

		$columns = implode(', ', $columns);

		$sql  = 'UPDATE ';
		$sql .= $this->wrap($this->query->table);
		$sql .= ' SET ';
		$sql .= $columns . ' ';
		$sql .= $this->wheres();

		return array('sql' => $sql, 'params' => array_merge(array_values($values), $this->params));
	}

	/**
	* Compiles a DELETE query.
	*
	* @access  public
	* @return  string
	*/

	public function delete()
	{
		$sql  = 'DELETE FROM ';
		$sql .= $this->wrap($this->query->table);
		$sql .= ' ';
		$sql .= $this->wheres();

		return array('sql' => $sql, 'params' => $this->params);
	}
}

/** -------------------- End of file --------------------**/