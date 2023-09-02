<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\compilers\traits\JsonPathBuilderTrait;

use function str_replace;

/**
 * Compiles MySQL queries.
 */
class MySQL extends Compiler
{
	use JsonPathBuilderTrait;

	/**
	 * {@inheritDoc}
	 */
	protected static string $dateFormat = 'Y-m-d H:i:s';

	/**
	 * {@inheritDoc}
	 */
	public function escapeIdentifier(string $identifier): string
	{
		return '`' . str_replace('`', '``', $identifier) . '`';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function buildJsonGet(string $column, array $segments): string
	{
		return "{$column}->>'{$this->buildJsonPath($segments)}'";
	}

	/**
	 * {@inheritDoc}
	 */
	protected function buildJsonSet(string $column, array $segments, string $param): string
	{
		return "{$column} = JSON_SET({$column}, '{$this->buildJsonPath($segments)}', CAST({$param} AS JSON))";
	}

	/**
	 * {@inheritDoc}
	 */
	protected function betweenDate(array $where): string
	{
		$date1 = "{$where['value1']} 00:00:00.000000";
		$date2 = "{$where['value2']} 23:59:59.999999";

		return $this->columnName($where['column']) . ($where['not'] ? ' NOT BETWEEN ' : ' BETWEEN ') . "{$this->simpleParam($date1)} AND {$this->simpleParam($date2)}";
	}

	/**
	 * {@inheritDoc}
	 */
	protected function whereDate(array $where): string
	{
		switch($where['operator'])
		{
			case '=':
			case '!=':
			case '<>':
				$where =
				[
					'column' => $where['column'],
					'not'    => $where['operator'] !== '=',
					'value1' => $where['value'],
					'value2' => $where['value'],
				];

				return $this->betweenDate($where);
			case '>':
			case '>=':
			case '<':
			case '<=':
				switch($where['operator'])
				{
					case '>=':
					case '<':
						$suffix = ' 00:00:00.000000';
						break;
					default:
						$suffix = ' 23:59:59.999999';
				}

				return "{$this->columnName($where['column'])} {$where['operator']} {$this->simpleParam("{$where['value']}{$suffix}")}";
			default:
				return "DATE({$this->columnName($where['column'])}) {$where['operator']} {$this->simpleParam($where['value'])}";
		}
	}

	/**
	 * {@inheritDoc}
	 */
	protected function offset(?int $offset): string
	{
		if($offset === null)
		{
			return '';
		}

		return ($this->query->getLimit() === null) ? " LIMIT 18446744073709551615 OFFSET {$offset}" : " OFFSET {$offset}";
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

		return $lock === true ? ' FOR UPDATE' : ($lock === false ? ' LOCK IN SHARE MODE' : " {$lock}");
	}

	/**
	 * {@inheritDoc}
	 */
	protected function insertWithoutValues(): string
	{
		return "INSERT INTO {$this->escapeTableName($this->query->getTable())} () VALUES ()";
	}
}
