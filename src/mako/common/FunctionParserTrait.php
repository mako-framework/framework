<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\common;

use RuntimeException;

/**
 * Function parser trait.
 *
 * @author  Frederic G. Østby
 */

trait FunctionParserTrait
{
	/**
	 * Parses custom "function calls".
	 *
	 * The return value is an array consisting of the function name and parameters.
	 *
	 * @access  protected
	 * @param   string     $function  Function
	 * @return  array
	 */

	protected function parseFunction($function)
	{
		if(strpos($function, ':') === false)
		{
			return [$function, []];
		}

		list($function, $parameters) = explode(':', $function, 2);

		$parameters = (array) json_decode($parameters, true);

		if(json_last_error() !== JSON_ERROR_NONE)
		{
			throw new RuntimeException(vsprintf('%s(): Function parameters must be valid JSON.', [__METHOD__]));
		}

		return [$function, $parameters];
	}
}