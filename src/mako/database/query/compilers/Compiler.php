<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use DateTimeInterface;
use RuntimeException;

use mako\database\query\Join;
use mako\database\query\Query;
use mako\database\query\Raw;
use mako\database\query\Subquery;

/**
 * Compiles SQL queries.
 *
 * @author  Frederic G. Østby
 */
class Compiler
{
	/**
	 * Date format.
	 *
	 * @var string
	 */
	protected static $dateFormat = 'Y-m-d H:i:s';

	/**
	 * Query builder.
	 *
	 * @var mako\database\query\Query
	 */
	protected $query;

	/**
	 * Query parameters.
	 *
	 * @var array
	 */
	protected $params = [];

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\database\query\Query  $query  Query builder
	 */
	public function __construct(Query $query)
	{
		$this->query = $query;
	}

	/**
	 * Sets the date format.
	 *
	 * @access  public
	 * @param   string  $dateFormat  Date format
	 * @return  string
	 */
	public static function setDateFormat($dateFormat)
	{
		static::$dateFormat = $dateFormat;
	}

	/**
	 * Gets the date format.
	 *
	 * @access  public
	 * @return  string
	 */
	public static function getDateFormat()
	{
		return static::$dateFormat;
	}

	/**
	 * Checks whether a string is between parentheses.
	 *
	 * @access  protected
	 * @param   string     $string The string to check
	 * @return  boolean
	 */
	protected function isParenthesesEnclosed($string)
	{
		return strlen($string) >= 2 && substr($string, 0, 1) === '(' && substr($string, -1) === ')';
	}

	/**
	 * Compiles subquery, merges parameters and returns subquery SQL.
	 *
	 * @access  protected
	 * @param   \mako\database\query\Subquery  $query  Subquery container
	 * @return  string
	 */
	protected function subquery(Subquery $query)
	{
		$query = $query->build($this->query)->get();

		$this->params = array_merge($this->params, $query['params']);

		return $query['sql'];
	}

	/**
	 * Returns an escaped identifier.
	 *
	 * @access  public
	 * @param   string  $identifier  Identifier to escape
	 * @return  string
	 */
	public function escapeIdentifier($identifier)
	{
		return '"' . str_replace('"', '""', $identifier) . '"';
	}

	/**
	 * Builds a JSON path.
	 *
	 * @access  protected
	 * @param   string     $column    Column name
	 * @param   array      $segments  JSON path segments
	 * @return  string
	 */
	protected function buildJsonPath($column, array $segments)
	{
		throw new RuntimeException(vsprintf("%s(): The [ %s ] query compiler does not support the unified JSON field syntax.", [__METHOD__, static::class]));
	}

	/**
	 * Returns an escaped table or column name.
	 *
	 * @access  public
	 * @param   string  $value  Value to escape
	 * @return  string
	 */
	public function wrapTableAndOrColumn($value)
	{
		$wrapped = [];

		if(strpos($value, '->') !== false)
		{
			list($value, $jsonPath) = explode('->', $value, 2);
		}

		foreach(explode('.', $value) as $segment)
		{
			if($segment === '*')
			{
				$wrapped[] = $segment;
			}
			else
			{
				$wrapped[] = $this->escapeIdentifier($segment);
			}
		}

		$wrapped = implode('.', $wrapped);

		if(isset($jsonPath))
		{
			$wrapped = $this->buildJsonPath($wrapped, explode('->', $jsonPath));
		}

		return $wrapped;
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
		elseif(stripos($value, ' AS ') !== false)
		{
			$values = explode(' ', $value);

			return sprintf('%s AS %s', $this->wrapTableAndOrColumn($values[0]), $this->wrapTableAndOrColumn($values[2]));
		}
		else
		{
			return $this->wrapTableAndOrColumn($value);
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
		return implode(', ', array_map([$this, 'wrap'], $columns));
	}

	/**
	 * Compiles the FROM clause.
	 *
	 * @access  public
	 * @param   string  $table  Table
	 * @return  string
	 */
	protected function from($table)
	{
		return ' FROM ' . $this->wrap($table);
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
		elseif($param instanceof DateTimeInterface)
		{
			$this->params[] = $param->format(static::$dateFormat);

			return '?';
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
		return implode(', ', array_map([$this, 'param'], $params));
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
		$values = $this->params($where['values']);

		return $this->wrap($where['column']) . ($where['not'] ? ' NOT IN ' : ' IN ') . ($this->isParenthesesEnclosed($values) ? $values : '(' . $values . ')');
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
	 * Compiles WHERE conditions.
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
	 * Compiles nested WHERE conditions.
	 *
	 * @access  protected
	 * @param   array      $where  Where clause
	 * @return  string
	 */
	protected function nestedWhere(array $where)
	{
		return '(' . $this->whereConditions($where['query']->getWheres()) . ')';
	}

	/**
	 * Compiles WHERE conditions.
	 *
	 * @access  protected
	 * @param   array      $wheres  Where conditions
	 * @return  string
	 */
	protected function whereConditions(array $wheres)
	{
		$conditions = [];

		$conditionCounter = 0;

		foreach($wheres as $where)
		{
			$conditions[] = ($conditionCounter > 0 ? $where['separator'] . ' ' : null) . $this->{$where['type']}($where);

			$conditionCounter++;
		}

		return implode(' ', $conditions);
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

		return ' WHERE ' . $this->whereConditions($wheres);
	}

	/**
	 * Compiles a JOIN condition.
	 *
	 * @access  protected
	 * @param   array  $condition  Join condition
	 * @return  string
	 */
	protected function joinCondition(array $condition)
	{
		return $this->wrap($condition['column1']) . ' ' . $condition['operator'] . ' ' . $this->wrap($condition['column2']);
	}

	/**
	 * Compiles nested JOIN condition.
	 *
	 * @access  protected
	 * @param   array  $condition  Join condition
	 * @return  string
	 */
	protected function nestedJoinCondition(array $condition)
	{
		$conditions = $this->joinConditions($condition['join']);

		return '(' . $conditions . ')';
	}

	/**
	 * Compiles JOIN conditions.
	 *
	 * @access  protected
	 * @param   \mako\database\query\Join  $join  Join
	 * @return  string
	 */
	protected function joinConditions(Join $join)
	{
		$conditions = [];

		$conditionCounter = 0;

		foreach($join->getConditions() as $condition)
		{
			$conditions[] = ($conditionCounter > 0 ? $condition['separator'] . ' ' : null) . $this->{$condition['type']}($condition);

			$conditionCounter++;
		}

		return implode(' ', $conditions);
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

		$sql = [];

		foreach($joins as $join)
		{
			$sql[] = $join->getType() . ' JOIN ' . $this->wrap($join->getTable()) . ' ON ' . $this->joinConditions($join);
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

		$sql = [];

		foreach($orderings as $order)
		{
			$sql[] = $this->columns($order['column']) . ' ' . $order['order'];
		}

		return ' ORDER BY ' . implode(', ', $sql);
	}

	/**
	 * Compiles HAVING conditions.
	 *
	 * @access  protected
	 * @param   array      $havings  Having conditions
	 * @return  string
	 */
	protected function havingCondictions(array $havings)
	{
		$conditions = [];

		$conditionCounter = 0;

		foreach($havings as $having)
		{
			$conditions[] = ($conditionCounter > 0 ? $having['separator'] . ' ' : null) . $this->wrap($having['column']) . ' ' . $having['operator'] . ' ' . $this->param($having['value']);

			$conditionCounter++;
		}

		return implode(' ', $conditions);
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

		return ' HAVING ' . $this->havingCondictions($havings);
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
	 * Compiles locking clause.
	 *
	 * @access  protected
	 * @param   null|boolean|string
	 * @return  string
	 */
	protected function lock($lock)
	{
		return '';
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
		$sql .= $this->from($this->query->getTable());
		$sql .= $this->joins($this->query->getJoins());
		$sql .= $this->wheres($this->query->getWheres());
		$sql .= $this->groupings($this->query->getGroupings());
		$sql .= $this->havings($this->query->getHavings());
		$sql .= $this->orderings($this->query->getOrderings());
		$sql .= $this->limit($this->query->getLimit());
		$sql .= $this->offset($this->query->getOffset());
		$sql .= $this->lock($this->query->getLock());

		return ['sql' => $sql, 'params' => $this->params];
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

		return ['sql' => $sql, 'params' => $this->params];
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
		$columns = [];

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

		return ['sql' => $sql, 'params' => $this->params];
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

		return ['sql' => $sql, 'params' => $this->params];
	}
}