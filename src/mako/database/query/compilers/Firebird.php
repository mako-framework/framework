<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

/**
 * Compiles Firebird queries.
 *
 * @author Frederic G. Østby
 */
class Firebird extends Compiler
{
	/**
	 * {@inheritdoc}
	 */
	protected static $dateFormat = 'Y-m-d H:i:s';

	/**
	 * {@inheritdoc}
	 */
	protected function betweenDate(array $where): string
	{
		return "CAST({$this->columnName($where['column'])} AS DATE)" . ($where['not'] ? ' NOT BETWEEN ' : ' BETWEEN ') . "{$this->param($where['value1'])} AND {$this->param($where['value2'])}";
	}

	/**
	 * {@inheritdoc}
	 */
	protected function whereDate(array $where): string
	{
		return "CAST({$this->columnName($where['column'])} AS DATE) {$where['operator']} {$this->param($where['value'])}";
	}

	/**
	 * {@inheritdoc}
	 */
	protected function limit(?int $limit): string
	{
		$offset = $this->query->getOffset();

		return ($offset === null) ? (($limit === null) ? '' : ' ROWS 1') : ' ROWS ' . ($offset + 1);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function offset(?int $offset): string
	{
		$limit = $this->query->getLimit();

		return ($limit === null) ? '' : ' TO ' . ($limit + (($offset === null) ? 0 : $offset));
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

		return $lock === true ? ' FOR UPDATE WITH LOCK' : ($lock === false ? ' WITH LOCK' : " {$lock}");
	}
}
