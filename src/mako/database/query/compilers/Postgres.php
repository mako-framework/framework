<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\compilers\Compiler;

/**
 * Compiles Postgres queries.
 *
 * @author  Frederic G. Østby
 * @author  Yamada Taro
 */
class Postgres extends Compiler
{
	/**
	 * {@inheritdoc}
	 */
	protected function buildJsonPath($column, array $segments)
	{
		$segments = array_map(function($segment)
		{
			if(is_numeric($segment))
			{
				return	$segment;
			}

			return "'" . str_replace("'", "''", $segment) . "'";
		}, $segments);

		$last = array_pop($segments);

		if(empty($segments))
		{
			return $column . $path = '->>' . $last;
		}

		return $column . '->' . implode('->', $segments) . '->>' . $last;
	}

	/**
	 * {@inheritdoc}
	 */
	public function lock($lock)
	{
		if($lock === null)
		{
			return '';
		}

		return $lock === true ? ' FOR UPDATE' : ($lock === false ? ' FOR SHARE' : ' ' . $lock);
	}
}