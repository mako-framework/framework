<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\compilers\traits;

use function is_numeric;
use function str_replace;

/**
 * JSON path builder trait.
 *
 * @author Frederic G. Østby
 */
trait JsonPathBuilderTrait
{
	/**
	 * Builds a JSON path.
	 *
	 * @param  array  $segments Path segments
	 * @return string
	 */
	protected function buildJsonPath(array $segments): string
	{
		$path = '';

		foreach($segments as $segment)
		{
			if(is_numeric($segment))
			{
				$path .= "[{$segment}]";
			}
			else
			{
				$path .= '.' . '"' . str_replace(['"', "'"], ['\\\"', "''"], $segment) . '"';
			}
		}

		return "\${$path}";
	}
}
