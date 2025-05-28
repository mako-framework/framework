<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use DateTimeInterface;
use mako\database\exceptions\DatabaseException;
use mako\database\query\Join;
use mako\database\query\Query;
use mako\database\query\Raw;
use mako\database\query\Subquery;

use function array_keys;
use function array_shift;
use function explode;
use function implode;
use function is_array;
use function is_object;
use function sprintf;
use function str_replace;
use function stripos;
use function strpos;

/**
 * Compiles SQL queries.
 */
class Compiler
{
	/**
	 * JSON path separator.
	 */
	protected const string JSON_PATH_SEPARATOR = '->';

	/**
	 * Datetime format.
	 */
	protected static string $dateFormat = 'Y-m-d H:i:s';

	/**
	 * Query parameters.
	 */
	protected array $params = [];

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Query $query
	) {
	}

	/**
	 * Sets the date format.
	 */
	public static function setDateFormat(string $dateFormat): void
	{
		static::$dateFormat = $dateFormat;
	}

	/**
	 * Gets the date format.
	 */
	public static function getDateFormat(): string
	{
		return static::$dateFormat;
	}

	/**
	 * Compiles a raw SQL statement.
	 */
	protected function raw(Raw $raw): string
	{
		$parameters = $raw->getParameters();

		if (!empty($parameters)) {
			$this->params = [...$this->params, ...$parameters];
		}

		return $raw->getSql();
	}

	/**
	 * Compiles a subselect and merges the parameters.
	 */
	protected function subselect(Query $query): string
	{
		['sql' => $sql, 'params' => $params] = $query->getCompiler()->select();

		$this->params = [...$this->params, ...$params];

		return $sql;
	}

	/**
	 * Compiles a subquery.
	 */
	protected function subquery(Subquery $subquery, bool $enclose = true): string
	{
		$builder = $subquery->getQuery();

		$query = $subquery->providesBuilderInstance() ? null : $this->query->newInstance()->inSubqueryContext();

		$builder($query);

		$sql = $this->subselect($query);

		if ($enclose) {
			$sql = "({$sql})";

			if (($alias = $subquery->getAlias()) !== null) {
				$sql .= " AS {$this->escapeIdentifier($alias)}";
			}
		}

		return $sql;
	}

	/**
	 * Returns an escaped identifier.
	 */
	public function escapeIdentifier(string $identifier): string
	{
		return '"' . str_replace('"', '""', $identifier) . '"';
	}

	/**
	 * Returns a comma-separated list of escaped identifiers.
	 */
	public function escapeIdentifiers(array $identifiers): string
	{
		foreach ($identifiers as $key => $identifier) {
			$identifiers[$key] = $this->escapeIdentifier($identifier);
		}

		return implode(', ', $identifiers);
	}

	/**
	 * Does the string have a JSON path?
	 */
	protected function hasJsonPath(string $string): bool
	{
		return strpos($string, static::JSON_PATH_SEPARATOR) !== false;
	}

	/**
	 * Builds a JSON value getter.
	 */
	protected function buildJsonGet(string $column, array $segments): string
	{
		throw new DatabaseException(sprintf('The [ %s ] query compiler does not support the unified JSON field syntax.', static::class));
	}

	/**
	 * Builds a JSON value setter.
	 */
	protected function buildJsonSet(string $column, array $segments, string $param): string
	{
		throw new DatabaseException(sprintf('The [ %s ] query compiler does not support the unified JSON field syntax.', static::class));
	}

	/**
	 * Escapes a table name.
	 */
	public function escapeTableName(string $table): string
	{
		$segments = [];

		foreach (explode('.', $table) as $segment) {
			$segments[] = $this->escapeIdentifier($segment);
		}

		return implode('.', $segments);
	}

	/**
	 * Returns a comma-separated list of escaped table names.
	 */
	public function escapeTableNames(array $tables): string
	{
		$sql = [];

		foreach ($tables as $table) {
			$sql[] = $this->escapeTableName($table);
		}

		return implode(', ', $sql);
	}

	/**
	 * Compiles a table.
	 */
	public function table(Raw|string|Subquery $table): string
	{
		if ($table instanceof Raw) {
			return $this->raw($table);
		}
		elseif ($table instanceof Subquery) {
			return $this->subquery($table);
		}
		elseif (stripos($table, ' AS ') !== false) {
			[$table, , $alias] = explode(' ', $table, 3);

			return "{$this->escapeTableName($table)} AS {$this->escapeTableName($alias)}";
		}

		return $this->escapeTableName($table);
	}

	/**
	 * Returns a comma-separated list of tables.
	 */
	public function tables(array $tables): string
	{
		$sql = [];

		foreach ($tables as $table) {
			$sql[] = $this->table($table);
		}

		return implode(', ', $sql);
	}

	/**
	 * Escapes a column name.
	 */
	public function escapeColumnName(string $column): string
	{
		$segments = [];

		foreach (explode('.', $column) as $segment) {
			if ($segment === '*') {
				$segments[] = $segment;
			}
			else {
				$segments[] = $this->escapeIdentifier($segment);
			}
		}

		return implode('.', $segments);
	}

	/**
	 * Compiles a column name.
	 */
	public function columnName(string $column): string
	{
		if ($this->hasJsonPath($column)) {
			$segments = explode(static::JSON_PATH_SEPARATOR, $column);

			$column = $this->escapeColumnName(array_shift($segments));

			return $this->buildJsonGet($column, $segments);
		}

		return $this->escapeColumnName($column);
	}

	/**
	 * Returns a comma-separated list of column names.
	 */
	public function columnNames(array $columns): string
	{
		$sql = [];

		foreach ($columns as $column) {
			$sql[] = $this->columnName($column);
		}

		return implode(', ', $sql);
	}

	/**
	 * Compiles a column.
	 */
	public function column(Raw|string|Subquery $column, bool $allowAlias = false): string
	{
		if ($column instanceof Raw) {
			return $this->raw($column);
		}
		elseif ($column instanceof Subquery) {
			return $this->subquery($column);
		}
		elseif ($allowAlias && stripos($column, ' AS ') !== false) {
			[$column, , $alias] = explode(' ', $column, 3);

			return "{$this->columnName($column)} AS {$this->columnName($alias)}";
		}

		return $this->columnName($column);
	}

	/**
	 * Returns a comma-separated list of compiled columns.
	 */
	public function columns(array $columns, bool $allowAlias = false): string
	{
		$sql = [];

		foreach ($columns as $column) {
			$sql[] = $this->column($column, $allowAlias);
		}

		return implode(', ', $sql);
	}

	/**
	 * Compiles common table expressions.
	 */
	protected function commonTableExpressions(array $commonTableExpressions): string
	{
		['recursive' => $recursive, 'ctes' => $ctes] = $commonTableExpressions;

		if (empty($ctes)) {
			return '';
		}

		$expressions = [];

		foreach ($ctes as $cte) {
			$expression = $this->escapeIdentifier($cte['name']);

			if (empty($cte['columns']) === false) {
				$expression .= " ({$this->escapeIdentifiers($cte['columns'])})";
			}

			$expressions[] = "{$expression} AS ({$this->subquery($cte['query'], false)})";
		}

		return ($recursive ? 'WITH RECURSIVE ' : 'WITH ') . implode(', ', $expressions) . ' ';
	}

	/**
	 * Compiles set operations.
	 */
	protected function setOperations(array $setOperations): string
	{
		if (empty($setOperations)) {
			return '';
		}

		$sql = '';

		foreach ($setOperations as $setOperation) {
			$sql .= "{$this->subselect($setOperation['query'])} {$setOperation['operation']} ";
		}

		return $sql;
	}

	/**
	 * Returns raw SQL or a paramter placeholder.
	 */
	protected function param(mixed $param, bool $enclose = true): string
	{
		if (is_object($param)) {
			if ($param instanceof Raw) {
				return $this->raw($param);
			}
			elseif ($param instanceof Subquery) {
				return $this->subquery($param, $enclose);
			}
			elseif ($param instanceof DateTimeInterface) {
				$this->params[] = $param->format(static::$dateFormat);

				return '?';
			}
		}

		$this->params[] = $param;

		return '?';
	}

	/**
	 * Returns a comma-separated list of parameters.
	 */
	protected function params(array $params, bool $enclose = true): string
	{
		$sql = [];

		foreach ($params as $param) {
			$sql[] = $this->param($param, $enclose);
		}

		return implode(', ', $sql);
	}

	/**
	 * Returns a parameter placeholder.
	 */
	protected function simpleParam(mixed $param): string
	{
		$this->params[] = $param;

		return '?';
	}

	/**
	 * Returns a comma-separated list of parameter placeholders.
	 */
	protected function simpleParams(array $params): string
	{
		$sql = [];

		foreach ($params as $param) {
			$sql[] = $this->simpleParam($param);
		}

		return implode(', ', $sql);
	}

	/**
	 * Compiles the FROM clause.
	 */
	protected function from(null|array|Raw|string|Subquery $table): string
	{
		if ($table === null) {
			return '';
		}

		return ' FROM ' . (is_array($table) ? $this->tables($table) : $this->table($table));
	}

	/**
	 * Compiles WHERE conditions.
	 */
	protected function where(array $where): string
	{
		if (is_array($where['column'])) {
			$column = "({$this->columnNames($where['column'])})";

			$value = is_array($where['value']) ? "({$this->params($where['value'])})" : $this->param($where['value']);

			return "{$column} {$where['operator']} {$value}";
		}

		return "{$this->column($where['column'])} {$where['operator']} {$this->param($where['value'])}";
	}

	/**
	 * Compiles a raw WHERE condition.
	 */
	protected function whereRaw(array $where): string
	{
		return $this->raw($where['raw']);
	}

	/**
	 * Compiles date comparison clauses.
	 */
	protected function whereDate(array $where): string
	{
		throw new DatabaseException(sprintf('The [ %s ] query compiler does not support date comparisons.', static::class));
	}

	/**
	 * Compiles column comparison clauses.
	 */
	protected function whereColumn(array $where): string
	{
		if (is_array($where['column1'])) {
			$column1 = "({$this->columnNames($where['column1'])})";

			$column2 = is_array($where['column2']) ? "({$this->columnNames($where['column2'])})" : $this->columnName($where['column2']);

			return "{$column1} {$where['operator']} {$column2}";
		}

		return "{$this->columnName($where['column1'])} {$where['operator']} {$this->columnName($where['column2'])}";
	}

	/**
	 * Compiles BETWEEN clauses.
	 */
	protected function between(array $where): string
	{
		return $this->column($where['column']) . ($where['not'] ? ' NOT BETWEEN ' : ' BETWEEN ') . "{$this->param($where['value1'])} AND {$this->param($where['value2'])}";
	}

	/**
	 * Compiles date range clauses.
	 */
	protected function betweenDate(array $where): string
	{
		throw new DatabaseException(sprintf('The [ %s ] query compiler does not support date ranges.', static::class));
	}

	/**
	 * Compiles IN clauses.
	 */
	protected function in(array $where): string
	{
		$values = $this->params($where['values'], false);

		return $this->column($where['column']) . ($where['not'] ? ' NOT IN ' : ' IN ') . "({$values})";
	}

	/**
	 * Compiles IS NULL clauses.
	 */
	protected function null(array $where): string
	{
		return $this->column($where['column']) . ($where['not'] ? ' IS NOT NULL' : ' IS NULL');
	}

	/**
	 * Compiles EXISTS clauses.
	 */
	protected function exists(array $where): string
	{
		return ($where['not'] ? 'NOT EXISTS ' : 'EXISTS ') . $this->subquery($where['query']);
	}

	/**
	 * Compiles nested WHERE conditions.
	 */
	protected function nestedWhere(array $where): string
	{
		return "({$this->whereConditions($where['query']->getWheres())})";
	}

	/**
	 * Compiles WHERE conditions.
	 */
	protected function whereConditions(array $wheres): string
	{
		$conditions = [];

		$conditionCounter = 0;

		foreach ($wheres as $where) {
			$conditions[] = ($conditionCounter > 0 ? "{$where['separator']} " : '') . $this->{$where['type']}($where);

			$conditionCounter++;
		}

		return implode(' ', $conditions);
	}

	/**
	 * Compiles WHERE clauses.
	 */
	protected function wheres(array $wheres): string
	{
		if (empty($wheres)) {
			return '';
		}

		return " WHERE {$this->whereConditions($wheres)}";
	}

	/**
	 * Compiles a JOIN condition.
	 */
	protected function joinCondition(array $condition): string
	{
		if ($condition['operator'] === null) {
			return $this->column($condition['column1']);
		}

		return "{$this->column($condition['column1'])} {$condition['operator']} {$this->column($condition['column2'])}";
	}

	/**
	 * Compiles nested JOIN condition.
	 */
	protected function nestedJoinCondition(array $condition): string
	{
		$conditions = $this->joinConditions($condition['join']);

		return "({$conditions})";
	}

	/**
	 * Compiles JOIN conditions.
	 */
	protected function joinConditions(Join $join): string
	{
		$conditions = [];

		$conditionCounter = 0;

		foreach ($join->getConditions() as $condition) {
			$conditions[] = ($conditionCounter > 0 ? "{$condition['separator']} " : '') . $this->{$condition['type']}($condition);

			$conditionCounter++;
		}

		return implode(' ', $conditions);
	}

	/**
	 * Compiles JOIN clauses.
	 */
	protected function joins(array $joins): string
	{
		if (empty($joins)) {
			return '';
		}

		$sql = [];

		foreach ($joins as $join) {
			$sql[] = "{$join->getType()}"
			. " {$this->table($join->getTable())}"
			. ($join->hasConditions() ? " ON {$this->joinConditions($join)}" : '');
		}

		return ' ' . implode(' ', $sql);
	}

	/**
	 * Compiles GROUP BY clauses.
	 */
	protected function groupings(array $groupings): string
	{
		return empty($groupings) ? '' : " GROUP BY {$this->columns($groupings)}";
	}

	/**
	 * Compiles ORDER BY clauses.
	 */
	protected function orderings(array $orderings): string
	{
		if (empty($orderings)) {
			return '';
		}

		$sql = [];

		foreach ($orderings as $order) {
			$sql[] = "{$this->columns($order['column'])} {$order['order']}";
		}

		return ' ORDER BY ' . implode(', ', $sql);
	}

	/**
	 * Compiles HAVING conditions.
	 */
	protected function havingCondictions(array $havings): string
	{
		$conditions = [];

		$conditionCounter = 0;

		foreach ($havings as $having) {
			$conditions[] = ($conditionCounter > 0 ? "{$having['separator']} " : '') . "{$this->column($having['column'])} {$having['operator']} {$this->param($having['value'])}";

			$conditionCounter++;
		}

		return implode(' ', $conditions);
	}

	/**
	 * Compiles HAVING clauses.
	 */
	protected function havings(array $havings): string
	{
		if (empty($havings)) {
			return '';
		}

		return " HAVING {$this->havingCondictions($havings)}";
	}

	/**
	 * Compiles LIMIT clauses.
	 */
	protected function limit(?int $limit): string
	{
		return ($limit === null) ? '' : " LIMIT {$limit}";
	}

	/**
	 * Compiles OFFSET clauses.
	 */
	protected function offset(?int $offset): string
	{
		return ($offset === null) ? '' : " OFFSET {$offset}";
	}

	/**
	 * Compiles locking clause.
	 */
	protected function lock(null|bool|string $lock): string
	{
		return '';
	}

	/**
	 * Compiles a SELECT query.
	 *
	 * @return array{sql: string, params: array}
	 */
	public function select(): array
	{
		$sql = $this->query->getPrefix()
		. $this->setOperations($this->query->getSetOperations())
		. $this->commonTableExpressions($this->query->getCommonTableExpressions())
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
	 * Returns an INSERT query without values.
	 */
	protected function insertWithoutValues(): string
	{
		return "INSERT INTO {$this->escapeTableName($this->query->getTable())} DEFAULT VALUES";
	}

	/**
	 * Returns an INSERT query with values.
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
	 * Compiles an INSERT query.
	 *
	 * @return array{sql: string, params: array}
	 */
	public function insert(array $values = []): array
	{
		$sql = $this->query->getPrefix()
		. (empty($values) ? $this->insertWithoutValues() : $this->insertWithValues($values));

		return ['sql' => $sql, 'params' => $this->params];
	}

	/**
	 * Compiles an INSERT query with a RETURNING clause.
	 *
	 * @return array{sql: string, params: array}
	 */
	public function insertAndReturn(array $values, array $return): array
	{
		throw new DatabaseException(sprintf('The [ %s ] query compiler does not support insert and return queries.', static::class));
	}

	/**
	 * Compiles an INSERT query with multiple row inserts.
	 *
	 * @return array{sql: string, params: array}
	 */
	public function insertMultiple(array ...$values): array
	{
		$sql = $this->query->getPrefix()
		. "INSERT INTO {$this->escapeTableName($this->query->getTable())} "
		. "({$this->escapeIdentifiers(array_keys($values[0]))})"
		. ' VALUES ';

		$rows = [];

		foreach ($values as $rowValues) {
			$rows[] =  "({$this->params($rowValues)})";
		}

		$sql .= implode(', ', $rows);

		return ['sql' => $sql, 'params' => $this->params];
	}

	/**
	 * Compiles an INSERT OR UPDATE query.
	 *
	 * @return array{sql: string, params: array}
	 */
	public function insertOrUpdate(array $insertValues, array $updateValues, array $conflictTarget = []): array
	{
		throw new DatabaseException(sprintf('The [ %s ] query compiler does not support insert or update queries.', static::class));
	}

	/**
	 * Compiles update columns.
	 */
	protected function updateColumns(array $columns): string
	{
		$sql = [];

		foreach ($columns as $column => $value) {
			$param = $this->param($value);

			if ($this->hasJsonPath($column)) {
				$segments = explode(static::JSON_PATH_SEPARATOR, $column);

				$column = $this->escapeColumnName(array_shift($segments));

				$sql[] = $this->buildJsonSet($column, $segments, $param);
			}
			else {
				$sql[] = "{$this->escapeColumnName($column)} = {$param}";
			}
		}

		return implode(', ', $sql);
	}

	/**
	 * Compiles an UPDATE query.
	 *
	 * @return array{sql: string, params: array}
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
	 * Compiles an UPDATE query with a RETURNING clause.
	 *
	 * @return array{sql: string, params: array}
	 */
	public function updateAndReturn(array $values, array $return): array
	{
		throw new DatabaseException(sprintf('The [ %s ] query compiler does not support update and return queries.', static::class));
	}

	/**
	 * Compiles a DELETE query.
	 *
	 * @return array{sql: string, params: array}
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
