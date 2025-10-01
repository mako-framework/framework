<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\common\traits;

use mako\common\traits\exceptions\FunctionParserTraitException;

use function json_decode;
use function json_last_error;
use function preg_match;
use function sprintf;
use function str_contains;

/**
 * Function parser trait.
 */
trait FunctionParserTrait
{
	/**
	 * Splits function name and parameters into an array.
	 */
	protected function splitFunctionAndParameters(string $function): array
	{
		if (preg_match('/^([a-z0-9_:.\\\\]+)\((.*)\)$/i', $function, $matches) !== 1) {
			throw new FunctionParserTraitException(sprintf('[ %s ] does not match the expected function pattern.', $function));
		}

		return [$matches[1], $matches[2]];
	}

	/**
	 * Parses custom "function calls".
	 * The return value is an array consisting of the function name and parameters.
	 */
	protected function parseFunction(string $function, ?bool $namedParameters = null): array
	{
		if (str_contains($function, '(') === false) {
			return [$function, []];
		}

		$function = $this->splitFunctionAndParameters($function);

		if ($namedParameters === null) {
			$namedParameters = preg_match('/^"[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*"\s*:/', $function[1]) === 1;
		}

		$parameters = json_decode(($namedParameters === true ? "{{$function[1]}}" : "[{$function[1]}]"), true);

		if ($parameters === null && json_last_error() !== JSON_ERROR_NONE) {
			throw new FunctionParserTraitException('Failed to decode function parameters.');
		}

		return [$function[0], $parameters];
	}
}
