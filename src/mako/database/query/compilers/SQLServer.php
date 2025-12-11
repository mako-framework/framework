<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\compilers\traits\JsonPathBuilderTrait;
use mako\database\query\Raw;
use mako\database\query\Subquery;
use Override;

use function implode;
use function str_replace;
use function strpos;
use function substr_replace;

/**
 * Compiles SQL Server queries.
 */
class SQLServer extends Compiler
{
	use JsonPathBuilderTrait;

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected static string $dateFormat = 'Y-m-d H:i:s.0000000';

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function escapeIdentifier(string $identifier): string
	{
		return '[' . str_replace(']', ']]', $identifier) . ']';
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function buildJsonGet(string $column, array $segments): string
	{
		return "JSON_VALUE({$column}, 'lax {$this->buildJsonPath($segments)}')";
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function buildJsonSet(string $column, array $segments, string $param): string
	{
		return "{$column} = JSON_MODIFY({$column}, 'lax {$this->buildJsonPath($segments)}', JSON_QUERY('{$param}'))";
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function from(null|array|Raw|string|Subquery $from): string
	{
		$from = parent::from($from);

		if (($lock = $this->query->getLock()) !== null) {
			$from .= $lock === true ? ' WITH (UPDLOCK, ROWLOCK)' : ($lock === false ? ' WITH (HOLDLOCK, ROWLOCK)' : " {$lock}");
		}

		return $from;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function betweenDate(array $where): string
	{
		return "CAST({$this->columnName($where['column'])} AS DATE)" . ($where['not'] ? ' NOT BETWEEN ' : ' BETWEEN ') . "{$this->param($where['value1'])} AND {$this->param($where['value2'])}";
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function whereDate(array $where): string
	{
		return "CAST({$this->columnName($where['column'])} AS DATE) {$where['operator']} {$this->param($where['value'])}";
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function orderings(array $orderings): string
	{
		if (empty($orderings) && ($this->query->getLimit() !== null || $this->query->getOffset() !== null)) {
			return ' ORDER BY (SELECT 0)';
		}

		return parent::orderings($orderings);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function limit(?int $limit): string
	{
		if ($limit === null) {
			return '';
		}

		return ' OFFSET ' . ($this->query->getOffset() ?: 0) . " ROWS FETCH NEXT {$limit} ROWS ONLY";
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function offset(?int $offset): string
	{
		if ($this->query->getLimit() === null && $offset !== null) {
			return " OFFSET {$offset} ROWS";
		}

		return '';
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function insertAndReturn(array $values, array $return): array
	{
		foreach ($return as $key => $column) {
			$return[$key] = "INSERTED.{$this->columnName($column)}";
		}

		$query = $this->insert($values);

		$query['sql'] = substr_replace(
			$query['sql'],
			' OUTPUT ' . implode(', ', $return),
			strpos($query['sql'], empty($values) ? 'DEFAULT' : 'VALUES') - 1,
			0
		);

		return $query;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function insertMultipleAndReturn(array $return, array ...$values): array
	{
		foreach ($return as $key => $column) {
			$return[$key] = "INSERTED.{$this->columnName($column)}";
		}

		$query = $this->insertMultiple(...$values);

		$query['sql'] = substr_replace(
			$query['sql'],
			' OUTPUT ' . implode(', ', $return),
			strpos($query['sql'], 'VALUES') - 1,
			0
		);

		return $query;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function updateAndReturn(array $values, array $return): array
	{
		foreach ($return as $key => $column) {
			$return[$key] = "INSERTED.{$this->columnName($column)}";
		}

		$sql = $this->query->getPrefix()
		. 'UPDATE '
		. $this->escapeTableName($this->query->getTable())
		. ' SET '
		. $this->updateColumns($values)
		. ' OUTPUT ' . implode(', ', $return)
		. $this->wheres($this->query->getWheres());

		return ['sql' => $sql, 'params' => $this->params];
	}
}
