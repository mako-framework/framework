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

use function array_keys;
use function array_merge;
use function array_shift;
use function explode;
use function implode;
use function is_array;
use function str_replace;
use function stripos;
use function strpos;
use function vsprintf;

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
	 * Datetime format.
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

			$query = $this->query->newInstance()->inSubqueryContext();

			$builder($query);
		}

		['sql' => $sql, 'params' => $params] = $query->getCompiler()->select();

		$this->params = array_merge($this->params, $params);

		if($enclose)
		{
			$sql = "({$sql})";

			if(($alias = $subquery->getAlias()) !== null)
			{
				$sql .= " AS {$this->escapeIdentifier($alias)}";
			}
		}

		return $sql;
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
	 * @param  array  $identifiers Identifiers to escape
	 * @return string
	 */
	public function escapeIdentifiers(array $identifiers): string
	{
		foreach($identifiers as $key => $identifier)
		{
			$identifiers[$key] = $this->escapeIdentifier($identifier);
		}

		return implode(', ', $identifiers);
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
	public function escapeTableName(string $table): string
	{
		$segments = [];

		foreach(explode('.', $table) as $segment)
		{
			$segments[] = $this->escapeIdentifier($segment);
		}

		return implode('.', $segments);
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
			[$table, , $alias] = explode(' ', $table, 3);

			return "{$this->escapeTableName($table)} AS {$this->escapeTableName($alias)}";
		}

		return $this->escapeTableName($table);
	}

	/**
	 * Returns a comma-separated list of tables.
	 *
	 * @param  array  $tables Array of tables
	 * @return string
	 */
	public function tables(array $tables): string
	{
		$sql = [];

		foreach($tables as $table)
		{
			$sql[] = $this->table($table);
		}

		return implode(', ', $sql);
	}

	/**
	 * Escapes a column name.
	 *
	 * @param  string $column Column name
	 * @return string
	 */
	public function escapeColumnName(string $column): string
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
	public function columnName(string $column): string
	{
		if($this->hasJsonPath($column))
		{
			$segments = explode(static::JSON_PATH_SEPARATOR, $column);

			$column = $this->escapeColumnName(array_shift($segments));

			return $this->buildJsonGet($column, $segments);
		}

		return $this->escapeColumnName($column);
	}

	/**
	 * Returns a comma-separated list of column names.
	 *
	 * @param  array  $columns Array of column names
	 * @return string
	 */
	public function columnNames(array $columns): string
	{
		$pieces = [];

		foreach($columns as $column)
		{
			$pieces[] = $this->columnName($column);
		}

		return implode(', ', $pieces);
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
			[$column, , $alias] = explode(' ', $column, 3);

			return "{$this->columnName($column)} AS {$this->columnName($alias)}";
		}

		return $this->columnName($column);
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
	 * Compiles common table expressions.
	 *
	 * @param  array  $commonTableExpressions Common table expressions
	 * @return string
	 */
	protected function commonTableExpressions(array $commonTableExpressions): string
	{
		['recursive' => $recursive, 'ctes' => $ctes] = $commonTableExpressions;

		if(empty($ctes))
		{
			return '';
		}

		$expressions = [];

		foreach($ctes as $cte)
		{
			$expression = $this->escapeIdentifier($cte['name']);

			if(empty($cte['columns']) === false)
			{
				$expression .= " ({$this->escapeIdentifiers($cte['columns'])})";
			}

			$expressions[] = "{$expression} AS ({$this->subquery($cte['query'], false)})";
		}

		return ($recursive ? 'WITH RECURSIVE ' : 'WITH ') . implode(', ', $expressions) . ' ';
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
			$sql .= "{$this->subquery($setOperation['query'], false)} {$setOperation['operation']} ";
		}

		return $sql;
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
	 * Returns a parameter placeholder.
	 *
	 * @param  mixed  $param Parameter
	 * @return string
	 */
	protected function simpleParam($param): string
	{
		$this->params[] = $param;

		return '?';
	}

	/**
	 * Returns a comma-separated list of parameter placeholders.
	 *
	 * @param  array  $params Parameters
	 * @return string
	 */
	protected function simpleParams(array $params): string
	{
		$pieces = [];

		foreach($params as $param)
		{
			$pieces[] = $this->simpleParam($param);
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
		if($table === null)
		{
			return '';
		}

		return ' FROM ' . (is_array($table) ? $this->tables($table) : $this->table($table));
	}

	/**
	 * Compiles WHERE conditions.
	 *
	 * @param  array  $where Where clause
	 * @return string
	 */
	protected function where(array $where): string
	{
		if(is_array($where['column']))
		{
			$column = "({$this->columnNames($where['column'])})";

			$value = is_array($where['value']) ? "({$this->params($where['value'])})" : $this->param($where['value']);

			return "{$column} {$where['operator']} {$value}";
		}

		return "{$this->column($where['column'])} {$where['operator']} {$this->param($where['value'])}";
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
	 * Compiles date comparison clauses.
	 *
	 * @param  array  $where Where clause
	 * @return string
	 */
	protected function whereDate(array $where): string
	{
		throw new RuntimeException(vsprintf('The [ %s ] query compiler does not support date comparisons.', [static::class]));
	}

	/**
	 * Compiles column comparison clauses.
	 *
	 * @param  array  $where Where clause
	 * @return string
	 */
	protected function whereColumn(array $where): string
	{
		if(is_array($where['column1']))
		{
			$column1 = "({$this->columnNames($where['column1'])})";

			$column2 = is_array($where['column2']) ? "({$this->columnNames($where['column2'])})" : $this->columnName($where['column2']);

			return "{$column1} {$where['operator']} {$column2}";
		}

		return "{$this->columnName($where['column1'])} {$where['operator']} {$this->columnName($where['column2'])}";
	}

	/**
	 * Compiles BETWEEN clauses.
	 *
	 * @param  array  $where Where clause
	 * @return string
	 */
	protected function between(array $where): string
	{
		return $this->column($where['column']) . ($where['not'] ? ' NOT BETWEEN ' : ' BETWEEN ') . "{$this->param($where['value1'])} AND {$this->param($where['value2'])}";
	}

	/**
	 * Compiles date range clauses.
	 *
	 * @param  array  $where Where clause
	 * @return string
	 */
	protected function betweenDate(array $where): string
	{
		throw new RuntimeException(vsprintf('The [ %s ] query compiler does not support date ranges.', [static::class]));
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

		return $this->column($where['column']) . ($where['not'] ? ' NOT IN ' : ' IN ') . "({$values})";
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
	 * Compiles nested WHERE conditions.
	 *
	 * @param  array  $where Where clause
	 * @return string
	 */
	protected function nestedWhere(array $where): string
	{
		return "({$this->whereConditions($where['query']->getWheres())})";
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

		return " WHERE {$this->whereConditions($wheres)}";
	}

	/**
	 * Compiles a JOIN condition.
	 *
	 * @param  array  $condition Join condition
	 * @return string
	 */
	protected function joinCondition(array $condition): string
	{
		return "{$this->column($condition['column1'])} {$condition['operator']} {$this->column($condition['column2'])}";
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

		return "({$conditions})";
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
			$sql[] = "{$join->getType()} JOIN {$this->table($join->getTable())} ON {$this->joinConditions($join)}";
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
		return empty($groupings) ? '' : " GROUP BY {$this->columns($groupings)}";
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
			$sql[] = "{$this->columns($order['column'])} {$order['order']}";
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
			$conditions[] = ($conditionCounter > 0 ? $having['separator'] . ' ' : null) . "{$this->column($having['column'])} {$having['operator']} {$this->param($having['value'])}";

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

		return " HAVING {$this->havingCondictions($havings)}";
	}

	/**
	 * Compiles LIMIT clauses.
	 *
	 * @param  int|null $limit Limit
	 * @return string
	 */
	protected function limit(?int $limit): string
	{
		return ($limit === null) ? '' : " LIMIT {$limit}";
	}

	/**
	 * Compiles OFFSET clauses.
	 *
	 * @param  int|null $offset Limit
	 * @return string
	 */
	protected function offset(?int $offset): string
	{
		return ($offset === null) ? '' : " OFFSET {$offset}";
	}

	/**
	 * Compiles locking clause.
	 *
	 * @param  bool|string|null $lock Lock
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
		$sql = $this->query->getPrefix()
		. $this->commonTableExpressions($this->query->getCommonTableExpressions())
		. $this->setOperations($this->query->getSetOperations())
		. ($this->query->isDistinct() ? 'SELECT DISTINCT ' : 'SELECT ')
		. $this->columns($this->query->getColumns(), true)
		. $this->from($this->query->getTable())
		. $this->joins($this->query->getJoins())
		. $this->wheres($this->query->getWheres())
		. $this->groupings($this->query->getGroupings())
		. $this->havings($this->query->getHavings())
		. $this->orderings($this->query->getOrderings())
		. $this->limit($this->query->getLimit())
		. $this->offset($this->query->getOffset())
		. $this->lock($this->query->getLock());

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
		$sql = "INSERT INTO {$this->escapeTableName($this->query->getTable())} "
		. "({$this->escapeIdentifiers(array_keys($values))})"
		. ' VALUES '
		. "({$this->params($values)})";

		return $sql;
	}

	/**
	 * Returns a INSERT query without values.
	 *
	 * @return string
	 */
	protected function insertWithoutValues(): string
	{
		return "INSERT INTO {$this->escapeTableName($this->query->getTable())} DEFAULT VALUES";
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

				$column = $this->escapeColumnName(array_shift($segments));

				$pieces[] = $this->buildJsonSet($column, $segments, $param);
			}
			else
			{
				$pieces[] = "{$this->escapeColumnName($column)} = {$param}";
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
		$sql = $this->query->getPrefix()
		. 'UPDATE '
		. $this->escapeTableName($this->query->getTable())
		. ' SET '
		. $this->updateColumns($values)
		. $this->wheres($this->query->getWheres());

		return ['sql' => $sql, 'params' => $this->params];
	}

	/**
	 * Compiles a DELETE query.
	 *
	 * @return array
	 */
	public function delete(): array
	{
		$sql = $this->query->getPrefix()
		. 'DELETE FROM '
		. $this->escapeTableName($this->query->getTable())
		. $this->wheres($this->query->getWheres());

		return ['sql' => $sql, 'params' => $this->params];
	}
}
