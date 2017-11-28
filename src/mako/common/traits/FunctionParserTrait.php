<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\common\traits;

use RuntimeException;

/**
 * Function parser trait.
 *
 * @author Frederic G. Østby
 */
trait FunctionParserTrait
{
	/**
	 * Splits function name and parameters into an array.
	 *
	 * @param  string $function Function
	 * @return array
	 */
	protected function splitFunctionAndParameters(string $function): array
	{
		if(preg_match('/^([a-z0-9_:.]+)\((.*)\)$/i', $function, $matches) !== 1)
		{
			throw new RuntimeException(vsprintf('%s(): [ %s ] does not match the expected function pattern.', [__METHOD__, $function]));
		}

		return [$matches[1], $matches[2]];
	}

	/**
	 * Parses custom "function calls".
	 *
	 * The return value is an array consisting of the function name and parameters.
	 *
	 * @param  string $function        Function
	 * @param  bool   $namedParameters Are we expecting named parameters?
	 * @return array
	 */
	protected function parseFunction(string $function, bool $namedParameters = false): array
	{
		if(strpos($function, '(') === false)
		{
			return [$function, []];
		}

		$function = $this->splitFunctionAndParameters($function);

		$parameters = json_decode(($namedParameters ? '{' . $function[1] .'}' : '[' . $function[1] . ']'), true);

		if($parameters === null && json_last_error() !== JSON_ERROR_NONE)
		{
			throw new RuntimeException(vsprintf('%s(): Failed to decode function parameters.', [__METHOD__]));
		}

		return [$function[0], $parameters];
	}
}
