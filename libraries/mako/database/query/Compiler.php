<?php

namespace mako\database\query;

use \mako\database\query\Raw;

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
	* Query builder.
	*
	* @var mako\database\Query
	*/

	protected $query;

	/**
	* Query parameters.
	*
	* @var array
	*/

	protected $params = array();

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

	public function wrap($value)
	{
		if($value instanceof Raw)
		{
			return $value->get();
		}
		else
		{
			if(stripos($value, ' as ') !== false)
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
	* Returns a comma-separated list of columns.
	*
	* @access  protected
	* @param   array      Array of columns
	* @return  string
	*/

	protected function columns($columns)
	{
		return implode(', ', array_map(array($this, 'wrap'), $columns));
	}

	/**
	* Returns raw SQL or a paramter placeholder.
	*
	* @access  protected  
	* @param   mixed      Parameter
	* @return  string
	*/

	protected function param($param)
	{
		if($param instanceof Raw)
		{
			return $param->get();
		}
		else
		{
			$this->params[] = $param;

			return '?';
		}
	}

	/**
	* Returns a comma-separated list of parameters.
	*
	* @access  protected
	* @param   array   Array of parameters
	* @return  string
	*/

	protected function params($params)
	{
		return implode(', ', array_map(array($this, 'param'), $params));
	}

	/**
	* Compiles WHERE clauses.
	*
	* @access  protected
	* @param   array      Where clause
	* @return  string
	*/

	protected function where($where)
	{
		return $this->wrap($where['column']) . ' ' . $where['operator'] . ' ' . $this->param($where['value']);
	}

	/**
	* Compiles BETWEEN clauses.
	*
	* @access  protected
	* @param   array      Where clause
	* @return  string
	*/

	protected function between($where)
	{
		return $this->wrap($where['column']) . ' BETWEEN ' . $this->param($where['value1']) . ' AND ' . $this->param($where['value2']);
	}

	/**
	* Compiles IN clauses.
	*
	* @access  protected
	* @param   array      Where clause
	* @return  string
	*/

	protected function in($where)
	{
		return $this->wrap($where['column']) . ($where['not'] ? ' NOT IN ' : ' IN ') . '(' . $this->params($where['values']) . ')';
	}

	/**
	* Compiles IS NULL clauses.
	*
	* @access  protected
	* @param   array      Where clause
	* @return  string
	*/

	protected function null($where)
	{
		return $this->wrap($where['column']) . ($where['not'] ? ' IS NOT NULL' : ' IS NULL');
	}

	/**
	* Compiles nested WHERE clauses.
	*
	* @access  protected
	* @param   array      Where clause
	* @return  string
	*/

	protected function parenthesizedWhere($where)
	{
		return '(' . substr($this->wheres($where['query']->wheres), 7) . ')'; // substr to remove " WHERE "
	}

	/**
	* Compiles WHERE clauses.
	*
	* @access  protected
	* @param   array      Array of where clauses
	* @return  string
	*/

	protected function wheres($wheres)
	{
		if(empty($wheres))
		{
			return '';
		}

		$sql = array();

		foreach($wheres as $where)
		{
			$sql[] = $where['separator'] . ' ' . $this->$where['type']($where);
		}

		return ' WHERE ' . substr(implode(' ', $sql), 4); // substr to remove "AND "
	}

	/**
	* Compiles JOIN clauses.
	*
	* @access  protected
	* @param   array      Array of joins
	* @return  string
	*/

	protected function joins($joins)
	{
		if(empty($joins))
		{
			return '';
		}

		$sql = array();

		foreach($joins as $join)
		{
			$clauses = array();

			foreach($join->clauses as $clause)
			{
				$clauses[] = $clause['separator'] . ' ' . $this->wrap($clause['column1']) . ' ' . $clause['operator'] . ' ' . $this->wrap($clause['column2']);
			}

			$sql[] = $join->type . ' JOIN ' . $this->wrap($join->table) . ' ON ' . substr(implode(' ', $clauses), 4); // substr to remove "AND "
		}

		return ' ' . implode(' ', $sql);
	}

	/**
	* Compiles GROUP BY clauses.
	*
	* @access  protected
	* @return  string
	*/

	protected function groupings($groupings)
	{
		return empty($groupings) ? '' : ' GROUP BY ' . $this->columns($groupings);
	}

	/**
	* Compiles ORDER BY clauses.
	*
	* @access  protected
	* @param   array      Array of order by clauses
	* @return  string
	*/

	protected function orderings($orderings)
	{
		if(empty($orderings))
		{
			return '';
		}

		$sql = array();

		foreach($orderings as $order)
		{
			$sql[] = $this->wrap($order['column']) . ' ' . strtoupper($order['order']);
		}

		return ' ORDER BY ' . implode(', ', $sql);
	}

	/**
	* Compiles HAVING clauses.
	*
	* @access  protected
	* @param   array      Array of having clauses
	* @return  string
	*/

	protected function havings($havings)
	{
		if(empty($havings))
		{
			return '';
		}

		$sql = array();

		foreach($havings as $having)
		{
			$column  = ($having['column'] instanceof Raw) ? $having['column']->get() : $this->wrap($having['column']);

			$sql[] = $having['separator'] . ' ' . $column . ' ' . $having['operator'] . ' ' . $this->param($having['value']);; 
		}

		return ' HAVING ' . substr(implode(' ', $sql), 4); // substr to remove "AND "
	}

	/**
	* Compiles LIMIT clauses.
	*
	* @access  protected
	* @param   int        Limit
	* @return  string
	*/

	protected function limit($limit)
	{
		return empty($limit) ? '' : ' LIMIT ' . $limit;
	}

	/**
	* Compiles OFFSET clauses.
	*
	* @access  protected
	* @param   int        Limit
	* @return  string
	*/

	protected function offset($offset)
	{
		return empty($offset) ? '' : ' OFFSET ' . $offset;
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
		$sql .= $this->joins($this->query->joins);
		$sql .= $this->wheres($this->query->wheres);
		$sql .= $this->groupings($this->query->groupings);
		$sql .= $this->orderings($this->query->orderings);
		$sql .= $this->havings($this->query->havings);
		$sql .= $this->limit($this->query->limit);
		$sql .= $this->offset($this->query->offset);

		return array('sql' => $sql, 'params' => $this->params);
	}

	/**
	* Compiles a INSERT query.
	*
	* @access  public
	* @param   array   Array of values
	* @return  string
	*/

	public function insert($values)
	{
		$sql  = 'INSERT INTO ';
		$sql .= $this->wrap($this->query->table);
		$sql .= ' (' . $this->columns(array_keys($values)) . ')';
		$sql .= ' VALUES';
		$sql .= ' (' . $this->params($values) . ')';

		return array('sql' => $sql, 'params' => $this->params);
	}

	/**
	* Compiles a UPDATE query.
	*
	* @access  public
	* @param   array   Array of values
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
		$sql .= $columns;
		$sql .= $this->wheres($this->query->wheres);

		return array('sql' => $sql, 'params' => $this->params);
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
		$sql .= $this->wheres($this->query->wheres);

		return array('sql' => $sql, 'params' => $this->params);
	}
}

/** -------------------- End of file --------------------**/