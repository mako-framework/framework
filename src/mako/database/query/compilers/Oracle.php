<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\compilers\traits\JsonPathBuilderTrait;

/**
 * Compiles Oracle queries.
 *
 * @author Frederic G. Østby
 */
class Oracle extends Compiler
{
	use JsonPathBuilderTrait;

	/**
	 * Date format.
	 *
	 * @var string
	 */
	protected static $dateFormat = 'Y-m-d H:i:s';

	/**
	 * {@inheritdoc}
	 */
	protected function buildJsonGet(string $column, array $segments): string
	{
		return "JSON_VALUE({$column}, '{$this->buildJsonPath($segments)}')";
	}

	/**
	 * {@inheritdoc}
	 */
	protected function betweenDate(array $where): string
	{
		return "TO_CHAR({$this->column($where['column'])}, 'YYYY-MM-DD')" . ($where['not'] ? ' NOT BETWEEN ' : ' BETWEEN ') . "{$this->param($where['value1'])} AND {$this->param($where['value2'])}";
	}

	/**
	 * {@inheritdoc}
	 */
	protected function whereDate(array $where): string
	{
		return "TO_CHAR({$this->column($where['column'])}, 'YYYY-MM-DD') {$where['operator']} {$this->param($where['value'])}";
	}

	/**
	 * {@inheritdoc}
	 */
	public function lock($lock): string
	{
		if($lock === null)
		{
			return '';
		}

		return $lock === true ? ' FOR UPDATE' : ($lock === false ? ' FOR UPDATE' : " {$lock}");
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
	protected function limit(int $limit = null): string
	{
		$offset = $this->query->getOffset();

		if($limit === null)
		{
			return '';
		}

		if($offset === null)
		{
			return " FETCH FIRST {$limit} ROWS ONLY";
		}

		return " OFFSET {$offset} ROWS FETCH NEXT {$limit} ROWS ONLY";
	}

	/**
	 * {@inheritdoc}
	 */
	protected function offset(int $offset = null): string
	{
		$limit = $this->query->getLimit();

		if($limit === null && $offset !== null)
		{
			return " OFFSET {$offset} ROWS";
		}

		return '';
	}
}
