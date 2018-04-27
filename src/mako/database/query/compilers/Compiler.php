<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use Closure;
use DateTimeInterface;
use mako\database\query\Join;
use mako\database\query\Query;
use mako\database\query\Raw;
use mako\database\query\Subquery;
use RuntimeException;

/**
 * Compiles SQL queries.
 *
 * @author Frederic G. Østby
 */
class Compiler
{
	/**
	 * JSON path separator.
	 *
	 * @var string
	 */
	const JSON_PATH_SEPARATOR = '->';

	/**
	 * Date format.
	 *
	 * @var string
	 */
	protected static $dateFormat = 'Y-m-d H:i:s';

	/**
	 * Query builder.
	 *
	 * @var \mako\database\query\Query
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
	 * @param \mako\database\query\Query $query Query builder
	 */
	public function __construct(Query $query)
	{
		$this->query = $query;
	}

	/**
	 * Sets the date format.
	 *
	 * @param  string $dateFormat Date format
	 * @return string
	 */
	public static function setDateFormat(string $dateFormat)
	{
		static::$dateFormat = $dateFormat;
	}

	/**
	 * Gets the date format.
	 *
	 * @return string
	 */
	public static function getDateFormat(): string
	{
		return static::$dateFormat;
	}

	/**
	 * Compiles a raw SQL statement.
	 *
	 * @param  \mako\database\query\Raw $raw Raw SQL
	 * @return string
	 */
	protected function raw(Raw $raw): string
	{
		$parameters = $raw->getParameters();

		if(!empty($parameters))
		{
			$this->params = array_merge($this->params, $parameters);
		}

		return $raw->getSql();
	}

	/**
	 * Compiles subquery, merges parameters and returns subquery SQL.
	 *
	 * @param  \mako\database\query\Subquery $subquery Subquery container
	 * @param  bool                          $enclose  Should the subquery be enclosed in parentheses?
	 * @return string
	 */
	protected function subquery(Subquery $subquery, bool $enclose = true): string
	{
		$query = $subquery->getQuery();

		if($query instanceof Closure)
		{
			$builder = $query;

			$query = $this->query->newInstance();

			$builder($query);
		}

		$query = $query->getCompiler()->select();

		$this->params = array_merge($this->params, $query['params']);

		if($enclose)
		{
			$query['sql'] = '(' . $query['sql'] . ')';
		}

		if(($alias = $subquery->getAlias()) !== null)
		{
			$query['sql'] .= ' AS ' . $this->escapeIdentifier($alias);
		}

		return $query['sql'];
	}

	/**
	 * Returns an escaped identifier.
	 *
	 * @param  string $identifier Identifier to escape
	 * @return string
	 */
	public function escapeIdentifier(string $identifier): string
	{
		return '"' . str_replace('"', '""', $identifier) . '"';
	}

	/**
	 * Returns an array of escaped identifiers.
	 *
	 * @param  array $identifiers Identifiers to escape
	 * @return array
	 */
	public function escapeIdentifiers(array $identifiers): array
	{
		return array_map([$this, 'escapeIdentifier'], $identifiers);
	}

	/**
	 * Does the string have a JSON path?
	 *
	 * @param  string $string String
	 * @return bool
	 */
	protected function hasJsonPath(string $string): bool
	{
		return strpos($string, static::JSON_PATH_SEPARATOR) !== false;
	}

	/**
	 * Builds a JSON value getter.
	 *
	 * @param  string $column   Column name
	 * @param  array  $segments JSON path segments
	 * @return string
	 */
	protected function buildJsonGet(string $column, array $segments): string
	{
		throw new RuntimeException(vsprintf('The [ %s ] query compiler does not support the unified JSON field syntax.', [static::class]));
	}

	/**
	 * Builds a JSON value setter.
	 *
	 * @param  string $column   Column name
	 * @param  array  $segments JSON path segments
	 * @param  string $param    Parameter
	 * @return string
	 */
	protected function buildJsonSet(string $column, array $segments, string $param): string
	{
		throw new RuntimeException(vsprintf('The [ %s ] query compiler does not support the unified JSON field syntax.', [static::class]));
	}

	/**
	 * Escapes a table name.
	 *
	 * @param  string $table Table name
	 * @return string
	 */
	public function escapeTable(string $table): string
	{
		$segments = [];

		foreach(explode('.', $table) as $segment)
		{
			$segments[] = $this->escapeIdentifier($segment);
		}

		return implode('.', $segments);
	}

	/**
	 * Compiles set operations.
	 *
	 * @param  array  $setOperations Set operations
	 * @return string
	 */
	protected function setOperations(array $setOperations): string
	{
		if(empty($setOperations))
		{
			return '';
		}

		$sql = '';

		foreach($setOperations as $setOperation)
		{
			$sql .= $this->subquery($setOperation['query'], false) . ' ' . $setOperation['operation'] . ' ';
		}

		return $sql;
	}

	/**
	 * Compiles a table.
	 *
	 * @param  mixed  $table Table
	 * @return string
	 */
	public function table($table): string
	{
		if($table instanceof Raw)
		{
			return $this->raw($table);
		}
		elseif($table instanceof Subquery)
		{
			return $this->subquery($table);
		}
		elseif(stripos($table, ' AS ') !== false)
		{
			$table = explode(' ', $table, 3);

			return sprintf('%s AS %s', $this->escapeTable($table[0]), $this->escapeTable($table[2]));
		}

		return $this->escapeTable($table);
	}

	/**
	 * Escapes a column name.
	 *
	 * @param  string $column Column name
	 * @return string
	 */
	public function escapeColumn(string $column): string
	{
		$segments = [];

		foreach(explode('.', $column) as $segment)
		{
			if($segment === '*')
			{
				$segments[] = $segment;
			}
			else
			{
				$segments[] = $this->escapeIdentifier($segment);
			}
		}

		return implode('.', $segments);
	}

	/**
	 * Compiles a column name.
	 *
	 * @param  string $column Column name
	 * @return string
	 */
	protected function compileColumnName(string $column): string
	{
		if($this->hasJsonPath($column))
		{
			$segments = explode(static::JSON_PATH_SEPARATOR, $column);

			$column = $this->escapeColumn(array_shift($segments));

			return $this->buildJsonGet($column, $segments);
		}

		return $this->escapeColumn($column);
	}

	/**
	 * Compiles a column.
	 *
	 * @param  mixed  $column     Column
	 * @param  bool   $allowAlias Allow aliases?
	 * @return string
	 */
	public function column($column, bool $allowAlias = false): string
	{
		if($column instanceof Raw)
		{
			return $this->raw($column);
		}
		elseif($column instanceof Subquery)
		{
			return $this->subquery($column);
		}
		elseif($allowAlias && stripos($column, ' AS ') !== false)
		{
			$column = explode(' ', $column, 3);

			return sprintf('%s AS %s', $this->compileColumnName($column[0]), $this->compileColumnName($column[2]));
		}

		return $this->compileColumnName($column);
	}

	/**
	 * Returns a comma-separated list of compiled columns.
	 *
	 * @param  array  $columns    Array of columns
	 * @param  bool   $allowAlias Allow aliases?
	 * @return string
	 */
	public function columns(array $columns, bool $allowAlias = false): string
	{
		$pieces = [];

		foreach($columns as $column)
		{
			$pieces[] = $this->column($column, $allowAlias);
		}

		return implode(', ', $pieces);
	}

	/**
	 * Compiles the FROM clause.
	 *
	 * @param  mixed  $table Table
	 * @return string
	 */
	protected function from($table): string
	{
		return ' FROM ' . $this->table($table);
	}

	/**
	 * Returns raw SQL or a paramter placeholder.
	 *
	 * @param  mixed  $param   Parameter
	 * @param  bool   $enclose Should subqueries be enclosed in parentheses?
	 * @return string
	 */
	protected function param($param, bool $enclose = true): string
	{
		if($param instanceof Raw)
		{
			return $this->raw($param);
		}
		elseif($param instanceof Subquery)
		{
			return $this->subquery($param, $enclose);
		}
		elseif($param instanceof DateTimeInterface)
		{
			$this->params[] = $param->format(static::$dateFormat);

			return '?';
		}

		$this->params[] = $param;

		return '?';
	}

	/**
	 * Returns a comma-separated list of parameters.
	 *
	 * @param  array  $params  Array of parameters
	 * @param  bool   $enclose Should subqueries be enclosed in parentheses?
	 * @return string
	 */
	protected function params(array $params, bool $enclose = true): string
	{
		$pieces = [];

		foreach($params as $param)
		{
			$pieces[] = $this->param($param, $enclose);
		}

		return implode(', ', $pieces);
	}

	/**
	 * Compiles BETWEEN clauses.
	 *
	 * @param  array  $where Where clause
	 * @return string
	 */
	protected function between(array $where): string
	{
		return $this->column($where['column']) . ($where['not'] ? ' NOT BETWEEN ' : ' BETWEEN ') . $this->param($where['value1']) . ' AND ' . $this->param($where['value2']);
	}

	/**
	 * Compiles IN clauses.
	 *
	 * @param  array  $where Where clause
	 * @return string
	 */
	protected function in(array $where): string
	{
		$values = $this->params($where['values'], false);

		return $this->column($where['column']) . ($where['not'] ? ' NOT IN ' : ' IN ') . '(' . $values . ')';
	}

	/**
	 * Compiles IS NULL clauses.
	 *
	 * @param  array  $where Where clause
	 * @return string
	 */
	protected function null(array $where): string
	{
		return $this->column($where['column']) . ($where['not'] ? ' IS NOT NULL' : ' IS NULL');
	}

	/**
	 * Compiles EXISTS clauses.
	 *
	 * @param  array  $where Exists clause
	 * @return string
	 */
	protected function exists(array $where): string
	{
		return ($where['not'] ? 'NOT EXISTS ' : 'EXISTS ') . $this->subquery($where['query']);
	}

	/**
	 * Compiles WHERE conditions.
	 *
	 * @param  array  $where Where clause
	 * @return string
	 */
	protected function where(array $where): string
	{
		return $this->column($where['column']) . ' ' . $where['operator'] . ' ' . $this->param($where['value']);
	}

	/**
	 * Compiles a raw WHERE condition.
	 *
	 * @param  array  $where Where clause
	 * @return string
	 */
	protected function whereRaw(array $where): string
	{
		return $this->raw($where['raw']);
	}

	/**
	 * Compiles nested WHERE conditions.
	 *
	 * @param  array  $where Where clause
	 * @return string
	 */
	protected function nestedWhere(array $where): string
	{
		return '(' . $this->whereConditions($where['query']->getWheres()) . ')';
	}

	/**
	 * Compiles WHERE conditions.
	 *
	 * @param  array  $wheres Where conditions
	 * @return string
	 */
	protected function whereConditions(array $wheres): string
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
	 * @param  array  $wheres Array of where clauses
	 * @return string
	 */
	protected function wheres(array $wheres): string
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
	 * @param  array  $condition Join condition
	 * @return string
	 */
	protected function joinCondition(array $condition): string
	{
		return $this->column($condition['column1']) . ' ' . $condition['operator'] . ' ' . $this->column($condition['column2']);
	}

	/**
	 * Compiles nested JOIN condition.
	 *
	 * @param  array  $condition Join condition
	 * @return string
	 */
	protected function nestedJoinCondition(array $condition): string
	{
		$conditions = $this->joinConditions($condition['join']);

		return '(' . $conditions . ')';
	}

	/**
	 * Compiles JOIN conditions.
	 *
	 * @param  \mako\database\query\Join $join Join
	 * @return string
	 */
	protected function joinConditions(Join $join): string
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
	 * @param  array  $joins Array of joins
	 * @return string
	 */
	protected function joins(array $joins): string
	{
		if(empty($joins))
		{
			return '';
		}

		$sql = [];

		foreach($joins as $join)
		{
			$sql[] = $join->getType() . ' JOIN ' . $this->table($join->getTable()) . ' ON ' . $this->joinConditions($join);
		}

		return ' ' . implode(' ', $sql);
	}

	/**
	 * Compiles GROUP BY clauses.
	 *
	 * @param  array  $groupings Array of column names
	 * @return string
	 */
	protected function groupings(array $groupings): string
	{
		return empty($groupings) ? '' : ' GROUP BY ' . $this->columns($groupings);
	}

	/**
	 * Compiles ORDER BY clauses.
	 *
	 * @param  array  $orderings Array of order by clauses
	 * @return string
	 */
	protected function orderings(array $orderings): string
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
	 * @param  array  $havings Having conditions
	 * @return string
	 */
	protected function havingCondictions(array $havings): string
	{
		$conditions = [];

		$conditionCounter = 0;

		foreach($havings as $having)
		{
			$conditions[] = ($conditionCounter > 0 ? $having['separator'] . ' ' : null) . $this->column($having['column']) . ' ' . $having['operator'] . ' ' . $this->param($having['value']);

			$conditionCounter++;
		}

		return implode(' ', $conditions);
	}

	/**
	 * Compiles HAVING clauses.
	 *
	 * @param  array  $havings Array of having clauses
	 * @return string
	 */
	protected function havings(array $havings): string
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
	 * @param  int|null $limit Limit
	 * @return string
	 */
	protected function limit(int $limit = null): string
	{
		return ($limit === null) ? '' : ' LIMIT ' . $limit;
	}

	/**
	 * Compiles OFFSET clauses.
	 *
	 * @param  int|null $offset Limit
	 * @return string
	 */
	protected function offset(int $offset = null): string
	{
		return ($offset === null) ? '' : ' OFFSET ' . $offset;
	}

	/**
	 * Compiles locking clause.
	 *
	 * @param   bool|string|null
	 * @return string
	 */
	protected function lock($lock): string
	{
		return '';
	}

	/**
	 * Compiles a SELECT query.
	 *
	 * @return array
	 */
	public function select(): array
	{
		$sql  = $this->query->getPrefix();
		$sql .= $this->setOperations($this->query->getSetOperations());
		$sql .= $this->query->isDistinct() ? 'SELECT DISTINCT ' : 'SELECT ';
		$sql .= $this->columns($this->query->getColumns(), true);
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
	 * Returns a INSERT query with values.
	 *
	 * @param  array  $values Array of values
	 * @return string
	 */
	protected function insertWithValues(array $values): string
	{
		$sql  = 'INSERT INTO ';
		$sql .= $this->escapeTable($this->query->getTable());
		$sql .= ' (' . implode(', ', $this->escapeIdentifiers(array_keys($values))) . ')';
		$sql .= ' VALUES';
		$sql .= ' (' . $this->params($values) . ')';

		return $sql;
	}

	/**
	 * Returns a INSERT query without values.
	 *
	 * @return string
	 */
	protected function insertWithoutValues(): string
	{
		return 'INSERT INTO ' . $this->escapeTable($this->query->getTable()) . ' DEFAULT VALUES';
	}

	/**
	 * Compiles a INSERT query.
	 *
	 * @param  array $values Array of values
	 * @return array
	 */
	public function insert(array $values = []): array
	{
		$sql = $this->query->getPrefix();

		if(empty($values))
		{
			$sql .= $this->insertWithoutValues();
		}
		else
		{
			$sql .= $this->insertWithValues($values);
		}

		return ['sql' => $sql, 'params' => $this->params];
	}

	/**
	 * Compiles update columns.
	 *
	 * @param  array  $columns Associative array of columns and values
	 * @return string
	 */
	protected function updateColumns(array $columns): string
	{
		$pieces = [];

		foreach($columns as $column => $value)
		{
			$param = $this->param($value);

			if($this->hasJsonPath($column))
			{
				$segments = explode(static::JSON_PATH_SEPARATOR, $column);

				$column = $this->escapeColumn(array_shift($segments));

				$pieces[] = $this->buildJsonSet($column, $segments, $param);
			}
			else
			{
				$pieces[] = $this->escapeColumn($column) . ' = ' . $param;
			}
		}

		return implode(', ', $pieces);
	}

	/**
	 * Compiles a UPDATE query.
	 *
	 * @param  array $values Array of values
	 * @return array
	 */
	public function update(array $values): array
	{
		$sql  = $this->query->getPrefix();
		$sql .= 'UPDATE ';
		$sql .= $this->escapeTable($this->query->getTable());
		$sql .= ' SET ';
		$sql .= $this->updateColumns($values);
		$sql .= $this->wheres($this->query->getWheres());

		return ['sql' => $sql, 'params' => $this->params];
	}

	/**
	 * Compiles a DELETE query.
	 *
	 * @return array
	 */
	public function delete(): array
	{
		$sql  = $this->query->getPrefix();
		$sql .= 'DELETE FROM ';
		$sql .= $this->escapeTable($this->query->getTable());
		$sql .= $this->wheres($this->query->getWheres());

		return ['sql' => $sql, 'params' => $this->params];
	}
}
