<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako;

use function filter_var;
use function getenv;
use function json_encode;
use function substr;

/**
 * Builds and returns a "function" used for middleware, route constraints and validation rules.
 */
function f(string $_name, mixed ...$_arguments): string
{
	if(empty($_arguments))
	{
		return $_name;
	}

	return "{$_name}(" . substr(json_encode($_arguments), 1, -1) . ')';
}

/**
 * Returns the value of the chosen environment variable or NULL if it does not exist.
 */
function env(string $variableName, mixed $default = null, bool $isBool = false, bool $localOnly = false): mixed
{
	$value = $_ENV[$variableName] ?? getenv($variableName, $localOnly) ?: null;

    if($isBool && $value !== true && $value !== false && $value !== null)
    {
        $value = filter_var($value, FILTER_VALIDATE_BOOL);
    }

    return $value ?? $default;
}
