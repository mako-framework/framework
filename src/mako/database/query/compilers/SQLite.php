<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\compilers\traits\JsonPathBuilderTrait;

/**
 * Compiles SQLite queries.
 *
 * @author Frederic G. Østby
 * @author Yamada Taro
 */
class SQLite extends Compiler
{
	use JsonPathBuilderTrait;

	/**
	 * {@inheritdoc}
	 */
	protected static $dateFormat = 'Y-m-d H:i:s';

	/**
	 * {@inheritdoc}
	 */
	protected function buildJsonGet(string $column, array $segments): string
	{
		return "JSON_EXTRACT({$column}, '{$this->buildJsonPath($segments)}')";
	}

	/**
	 * {@inheritdoc}
	 */
	protected function buildJsonSet(string $column, array $segments, string $param): string
	{
		return $column . " = JSON_SET({$column}, '{$this->buildJsonPath($segments)}', JSON({$param}))";
	}

	/**
	 * {@inheritdoc}
	 */
	protected function betweenDate(array $where): string
	{
		$date1 = "{$where['value1']} 00:00:00.000";
		$date2 = "{$where['value2']} 23:59:59.999";

		return $this->compileColumnName($where['column']) . ($where['not'] ? ' NOT BETWEEN ' : ' BETWEEN ') . "{$this->simpleParam($date1)} AND {$this->simpleParam($date2)}";
	}

	/**
	 * {@inheritdoc}
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
						$suffix = ' 00:00:00.000';
						break;
					default:
						$suffix = ' 23:59:59.999';
				}

				return "{$this->compileColumnName($where['column'])} {$where['operator']} {$this->simpleParam("{$where['value']}{$suffix}")}";
			default:
				return "strftime('%Y-%m-%d', {$this->compileColumnName($where['column'])}) {$where['operator']} {$this->simpleParam($where['value'])}";
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function offset(?int $offset): string
	{
		if($offset === null)
		{
			return '';
		}

		return ($this->query->getLimit() === null) ? " LIMIT -1 OFFSET {$offset}" : " OFFSET {$offset}";
	}
}
