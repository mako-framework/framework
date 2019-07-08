<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\compilers\traits\JsonPathBuilderTrait;

use function str_replace;

/**
 * Compiles SQL Server queries.
 *
 * @author Frederic G. Østby
 */
class SQLServer extends Compiler
{
	use JsonPathBuilderTrait;

	/**
	 * {@inheritdoc}
	 */
	protected static $dateFormat = 'Y-m-d H:i:s.0000000';

	/**
	 * {@inheritdoc}
	 */
	public function escapeIdentifier(string $identifier): string
	{
		return '[' . str_replace(']', ']]', $identifier) . ']';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function buildJsonGet(string $column, array $segments): string
	{
		return "JSON_VALUE({$column}, 'lax {$this->buildJsonPath($segments)}')";
	}

	/**
	 * {@inheritdoc}
	 */
	protected function buildJsonSet(string $column, array $segments, string $param): string
	{
		return "{$column} = JSON_MODIFY({$column}, 'lax {$this->buildJsonPath($segments)}', JSON_QUERY('{$param}'))";
	}

	/**
	 * {@inheritdoc}
	 */
	public function from($from): string
	{
		$from = parent::from($from);

		if(($lock = $this->query->getLock()) !== null)
		{
			$from .= $lock === true ? ' WITH (UPDLOCK, ROWLOCK)' : ($lock === false ? ' WITH (HOLDLOCK, ROWLOCK)' : " {$lock}");
		}

		return $from;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function betweenDate(array $where): string
	{
		return "CAST({$this->column($where['column'])} AS DATE)" . ($where['not'] ? ' NOT BETWEEN ' : ' BETWEEN ') . "{$this->param($where['value1'])} AND {$this->param($where['value2'])}";
	}

	/**
	 * {@inheritdoc}
	 */
	protected function whereDate(array $where): string
	{
		return "CAST({$this->column($where['column'])} AS DATE) {$where['operator']} {$this->param($where['value'])}";
	}

	/**
	 * {@inheritdoc}
	 */
	protected function orderings(array $orderings): string
	{
		if(empty($orderings) && ($this->query->getLimit() !== null || $this->query->getOffset() !== null))
		{
			return ' ORDER BY (SELECT 0)';
		}

		return parent::orderings($orderings);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function limit(?int $limit): string
	{
		if($limit === null)
		{
			return '';
		}

		return ' OFFSET ' . ($this->query->getOffset() ?: 0) . " ROWS FETCH NEXT {$limit} ROWS ONLY";
	}

	/**
	 * {@inheritdoc}
	 */
	protected function offset(?int $offset): string
	{
		if($this->query->getLimit() === null && $offset !== null)
		{
			return " OFFSET {$offset} ROWS";
		}

		return '';
	}
}
