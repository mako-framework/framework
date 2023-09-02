<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\compilers\traits\JsonPathBuilderTrait;
use mako\database\query\Raw;
use mako\database\query\Subquery;

/**
 * Compiles Oracle queries.
 */
class Oracle extends Compiler
{
	use JsonPathBuilderTrait;

	/**
	 * {@inheritDoc}
	 */
	protected static string $dateFormat = 'Y-m-d H:i:s';

	/**
	 * {@inheritDoc}
	 */
	protected function buildJsonGet(string $column, array $segments): string
	{
		return "JSON_VALUE({$column}, '{$this->buildJsonPath($segments)}')";
	}

	/**
	 * {@inheritDoc}
	 */
	protected function from(array|Raw|Subquery|string|null $table): string
	{
		return $table === null ? ' FROM "DUAL"' : parent::from($table);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function betweenDate(array $where): string
	{
		return "TO_CHAR({$this->columnName($where['column'])}, 'YYYY-MM-DD')" . ($where['not'] ? ' NOT BETWEEN ' : ' BETWEEN ') . "{$this->param($where['value1'])} AND {$this->param($where['value2'])}";
	}

	/**
	 * {@inheritDoc}
	 */
	protected function whereDate(array $where): string
	{
		return "TO_CHAR({$this->columnName($where['column'])}, 'YYYY-MM-DD') {$where['operator']} {$this->param($where['value'])}";
	}

	/**
	 * {@inheritDoc}
	 */
	public function lock(bool|string|null $lock): string
	{
		if($lock === null)
		{
			return '';
		}

		return $lock === true ? ' FOR UPDATE' : ($lock === false ? ' FOR UPDATE' : " {$lock}");
	}

	/**
	 * {@inheritDoc}
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
	 * {@inheritDoc}
	 */
	protected function limit(?int $limit): string
	{
		if($limit === null)
		{
			return '';
		}

		$offset = $this->query->getOffset();

		if($offset === null)
		{
			return " FETCH FIRST {$limit} ROWS ONLY";
		}

		return " OFFSET {$offset} ROWS FETCH NEXT {$limit} ROWS ONLY";
	}

	/**
	 * {@inheritDoc}
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
