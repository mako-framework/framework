<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use function array_pop;
use function implode;
use function is_numeric;
use function str_replace;

/**
 * Compiles Postgres queries.
 *
 * @author Frederic G. Østby
 * @author Yamada Taro
 */
class Postgres extends Compiler
{
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
		$pieces = [];

		foreach($segments as $segment)
		{
			$pieces[] = is_numeric($segment) ? $segment : "'" . str_replace("'", "''", $segment) . "'";
		}

		$last = array_pop($pieces);

		if(empty($pieces))
		{
			return "{$column}->>{$last}";
		}

		return "{$column}->" . implode('->', $pieces) . "->>{$last}";
	}

	/**
	 * {@inheritdoc}
	 */
	protected function buildJsonSet(string $column, array $segments, string $param): string
	{
		return $column . " = JSONB_SET({$column}, '{" . str_replace("'", "''", implode(',', $segments)) . "}', '{$param}')";
	}

	/**
	 * {@inheritdoc}
	 */
	protected function betweenDate(array $where): string
	{
		$date1 = "{$where['value1']} 00:00:00.000000";
		$date2 = "{$where['value2']} 23:59:59.999999";

		return $this->compileColumnName($where['column']) . ($where['not'] ? ' NOT BETWEEN ' : ' BETWEEN ') . "{$this->param($date1)} AND {$this->param($date2)}";
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
						$suffix = ' 00:00:00.000000';
						break;
					default:
						$suffix = ' 23:59:59.999999';
				}

				return "{$this->compileColumnName($where['column'])} {$where['operator']} {$this->param("{$where['value']}{$suffix}")}";
			default:
				return "{$this->compileColumnName($where['column'])}::date::char(10) {$where['operator']} {$this->param($where['value'])}";
		}
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

		return $lock === true ? ' FOR UPDATE' : ($lock === false ? ' FOR SHARE' : " {$lock}");
	}
}
