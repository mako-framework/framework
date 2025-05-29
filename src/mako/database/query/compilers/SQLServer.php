<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\compilers\traits\JsonPathBuilderTrait;
use mako\database\query\Raw;
use mako\database\query\Subquery;

use function implode;
use function str_replace;

/**
 * Compiles SQL Server queries.
 */
class SQLServer extends Compiler
{
	use JsonPathBuilderTrait;

	/**
	 * {@inheritDoc}
	 */
	protected static string $dateFormat = 'Y-m-d H:i:s.0000000';

	/**
	 * {@inheritDoc}
	 */
	public function escapeIdentifier(string $identifier): string
	{
		return '[' . str_replace(']', ']]', $identifier) . ']';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function buildJsonGet(string $column, array $segments): string
	{
		return "JSON_VALUE({$column}, 'lax {$this->buildJsonPath($segments)}')";
	}

	/**
	 * {@inheritDoc}
	 */
	protected function buildJsonSet(string $column, array $segments, string $param): string
	{
		return "{$column} = JSON_MODIFY({$column}, 'lax {$this->buildJsonPath($segments)}', JSON_QUERY('{$param}'))";
	}

	/**
	 * {@inheritDoc}
	 */
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
	protected function betweenDate(array $where): string
	{
		return "CAST({$this->columnName($where['column'])} AS DATE)" . ($where['not'] ? ' NOT BETWEEN ' : ' BETWEEN ') . "{$this->param($where['value1'])} AND {$this->param($where['value2'])}";
	}

	/**
	 * {@inheritDoc}
	 */
	protected function whereDate(array $where): string
	{
		return "CAST({$this->columnName($where['column'])} AS DATE) {$where['operator']} {$this->param($where['value'])}";
	}

	/**
	 * {@inheritDoc}
	 */
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
