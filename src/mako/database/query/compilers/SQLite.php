<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\compilers\Compiler;

/**
 * Compiles SQLite queries.
 *
 * @author  Frederic G. Østby
 * @author  Yamada Taro
 */
class SQLite extends Compiler
{
	/**
	 * {@inheritdoc}
	 */
	protected function buildJsonPath($column, array $segments)
	{
		$path = '';

		foreach($segments as $segment)
		{
			if(is_numeric($segment))
			{
				$path .= '[' . $segment . ']';
			}
			else
			{
				$path .= '.' . $segment;
			}
		}

		return 'json_extract(' . $column . ', ' . "'$" . str_replace("'", "''", $path) . "'" . ')';
	}
}