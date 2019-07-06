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
		return 'JSON_EXTRACT(' . $column . ", '" . $this->buildJsonPath($segments) . "')";
	}

	/**
	 * {@inheritdoc}
	 */
	protected function buildJsonSet(string $column, array $segments, string $param): string
	{
		return $column . ' = JSON_SET(' . $column . ", '" . $this->buildJsonPath($segments) . "', JSON(" . $param . '))';
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
					'value1' => $where['value'] . ' 00:00:00',
					'value2' => $where['value'] . ' 23:59:59',
				];

				return $this->between($where);
			case '>':
			case '>=':
			case '<':
			case '<=':
				switch($where['operator'])
				{
					case '>=':
					case '<':
						$suffix = ' 00:00:00';
						break;
					default:
						$suffix = ' 23:59:59';
				}

				return $this->column($where['column']) . ' ' . $where['operator'] . ' ' . $this->param($where['value'] . $suffix);
			default:
				return "strftime('%Y-%m-%d', " . $this->column($where['column']) . ') ' . $where['operator'] . ' ' . $this->param($where['value']);
		}
	}
}
