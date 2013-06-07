<?php

namespace mako\database\query;

use \mako\database\Query;
use \mako\database\query\Raw;
use \mako\database\query\Subquery;

/**
 * Compiles SQL queries.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Compiler
{
	//---------------------------------------------
	// Class properties
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
	 * @param   mako\database\Query  $query  Query builder
	 */

	public function __construct(Query $query)
	{
		$this->query = $query;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Compiles subquery, merges parameters and returns subquery SQL.
	 *
	 * @access  protected
	 * @param   mako\database\query\Subquery  $query  Subquery container
	 * @return  string
	 */

	protected function subquery(Subquery $query)
	{
		$query = $query->get();

		$this->params = array_merge($this->params, $query['params']);

		return $query['sql'];
	}

	/**
	 * Wraps table and column names with dialect specific escape characters.
	 *
	 * @access  public
	 * @param   mixed   $value  Value to wrap
	 * @return  string
	 */

	public function wrap($value)
	{
		if($value instanceof Raw)
		{
			return $value->get();
		}
		elseif($value instanceof Subquery)
		{
			return $this->subquery($value);
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
	 * @param   array      $columns  Array of columns
	 * @return  string
	 */

	protected function columns(array $columns)
	{
		return implode(', ', array_map(array($this, 'wrap'), $columns));
	}

	/**
	 * Returns raw SQL or a paramter placeholder.
	 *
	 * @access  protected  
	 * @param   mixed      $param  Parameter
	 * @return  string
	 */

	protected function param($param)
	{
		if($param instanceof Raw)
		{
			return $param->get();
		}
		elseif($param instanceof Subquery)
		{
			return $this->subquery($param);
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
	 * @param   mixed      $params  Array of parameters or subquery
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
	 * @param   array      $where  Where clause
	 * @return  string
	 */

	protected function where(array $where)
	{
		return $this->wrap($where['column']) . ' ' . $where['operator'] . ' ' . $this->param($where['value']);
	}

	/**
	 * Compiles BETWEEN clauses.
	 *
	 * @access  protected
	 * @param   array      $where  Where clause
	 * @return  string
	 */

	protected function between(array $where)
	{
		return $this->wrap($where['column']) . ($where['not'] ? ' NOT BETWEEN ' : ' BETWEEN ') . $this->param($where['value1']) . ' AND ' . $this->param($where['value2']);
	}

	/**
	 * Compiles IN clauses.
	 *
	 * @access  protected
	 * @param   array      $where  Where clause
	 * @return  string
	 */

	protected function in(array $where)
	{
		return $this->wrap($where['column']) . ($where['not'] ? ' NOT IN ' : ' IN ') . '(' . $this->params($where['values']) . ')';
	}

	/**
	 * Compiles IS NULL clauses.
	 *
	 * @access  protected
	 * @param   array      $where  Where clause
	 * @return  string
	 */

	protected function null(array $where)
	{
		return $this->wrap($where['column']) . ($where['not'] ? ' IS NOT NULL' : ' IS NULL');
	}

	/**
	 * Compiles EXISTS clauses.
	 *
	 * @access  protected
	 * @param   array      $where  Exists clause
	 * @return  string
	 */

	protected function exists($where)
	{
		return ($where['not'] ? 'NOT EXISTS ' : 'EXISTS ') . $this->subquery($where['query']);
	}

	/**
	 * Compiles nested WHERE clauses.
	 *
	 * @access  protected
	 * @param   array      $where  Where clause
	 * @return  string
	 */

	protected function nestedWhere(array $where)
	{
		return '(' . substr($this->wheres($where['query']->getWheres()), 7) . ')'; // substr to remove " WHERE "
	}

	/**
	 * Compiles WHERE clauses.
	 *
	 * @access  protected
	 * @param   array      $wheres  Array of where clauses
	 * @return  string
	 */

	protected function wheres(array $wheres)
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
	 * @param   array      $joins  Array of joins
	 * @return  string
	 */

	protected function joins(array $joins)
	{
		if(empty($joins))
		{
			return '';
		}

		$sql = array();

		foreach($joins as $join)
		{
			$clauses = array();

			foreach($join->getClauses() as $clause)
			{
				$clauses[] = $clause['separator'] . ' ' . $this->wrap($clause['column1']) . ' ' . $clause['operator'] . ' ' . $this->wrap($clause['column2']);
			}

			$sql[] = $join->getType() . ' JOIN ' . $this->wrap($join->getTable()) . ' ON ' . substr(implode(' ', $clauses), 4); // substr to remove "AND "
		}

		return ' ' . implode(' ', $sql);
	}

	/**
	 * Compiles GROUP BY clauses.
	 *
	 * @access  protected
	 * @param   array      $groupings  Array of column names
	 * @return  string
	 */

	protected function groupings(array $groupings)
	{
		return empty($groupings) ? '' : ' GROUP BY ' . $this->columns($groupings);
	}

	/**
	 * Compiles ORDER BY clauses.
	 *
	 * @access  protected
	 * @param   array      $orderings  Array of order by clauses
	 * @return  string
	 */

	protected function orderings(array $orderings)
	{
		if(empty($orderings))
		{
			return '';
		}

		$sql = array();

		foreach($orderings as $order)
		{
			$sql[] = $this->columns($order['column']) . ' ' . strtoupper($order['order']);
		}

		return ' ORDER BY ' . implode(', ', $sql);
	}

	/**
	 * Compiles HAVING clauses.
	 *
	 * @access  protected
	 * @param   array      $havings  Array of having clauses
	 * @return  string
	 */

	protected function havings(array $havings)
	{
		if(empty($havings))
		{
			return '';
		}

		$sql = array();

		foreach($havings as $having)
		{
			$sql[] = $having['separator'] . ' ' . $this->wrap($having['column']) . ' ' . $having['operator'] . ' ' . $this->param($having['value']);; 
		}

		return ' HAVING ' . substr(implode(' ', $sql), 4); // substr to remove "AND "
	}

	/**
	 * Compiles LIMIT clauses.
	 *
	 * @access  protected
	 * @param   int        $limit  Limit
	 * @return  string
	 */

	protected function limit($limit)
	{
		return ($limit === null) ? '' : ' LIMIT ' . $limit;
	}

	/**
	 * Compiles OFFSET clauses.
	 *
	 * @access  protected
	 * @param   int        $offset  Limit
	 * @return  string
	 */

	protected function offset($offset)
	{
		return ($offset === null) ? '' : ' OFFSET ' . $offset;
	}	

	/**
	 * Compiles a SELECT query.
	 *
	 * @access  public
	 * @return  array
	 */

	public function select()
	{
		$sql  = $this->query->isDistinct() ? 'SELECT DISTINCT ' : 'SELECT ';
		$sql .= $this->columns($this->query->getColumns());
		$sql .= ' FROM ';
		$sql .= $this->wrap($this->query->getTable());
		$sql .= $this->joins($this->query->getJoins());
		$sql .= $this->wheres($this->query->getWheres());
		$sql .= $this->groupings($this->query->getGroupings());
		$sql .= $this->orderings($this->query->getOrderings());
		$sql .= $this->havings($this->query->getHavings());
		$sql .= $this->limit($this->query->getLimit());
		$sql .= $this->offset($this->query->getOffset());

		return array('sql' => $sql, 'params' => $this->params);
	}

	/**
	 * Compiles a INSERT query.
	 *
	 * @access  public
	 * @param   array   $values  Array of values
	 * @return  array
	 */

	public function insert(array $values)
	{
		$sql  = 'INSERT INTO ';
		$sql .= $this->wrap($this->query->getTable());
		$sql .= ' (' . $this->columns(array_keys($values)) . ')';
		$sql .= ' VALUES';
		$sql .= ' (' . $this->params($values) . ')';

		return array('sql' => $sql, 'params' => $this->params);
	}

	/**
	 * Compiles a UPDATE query.
	 *
	 * @access  public
	 * @param   array   $values  Array of values
	 * @return  array
	 */

	public function update(array $values)
	{
		$columns = array();

		foreach($values as $column => $value)
		{
			$columns[] .= $this->wrap($column) . ' = ' . $this->param($value);
		}

		$columns = implode(', ', $columns);

		$sql  = 'UPDATE ';
		$sql .= $this->wrap($this->query->getTable());
		$sql .= ' SET ';
		$sql .= $columns;
		$sql .= $this->wheres($this->query->getWheres());

		return array('sql' => $sql, 'params' => $this->params);
	}

	/**
	 * Compiles a DELETE query.
	 *
	 * @access  public
	 * @return  array
	 */

	public function delete()
	{
		$sql  = 'DELETE FROM ';
		$sql .= $this->wrap($this->query->getTable());
		$sql .= $this->wheres($this->query->getWheres());

		return array('sql' => $sql, 'params' => $this->params);
	}
}

/** -------------------- End of file -------------------- **/