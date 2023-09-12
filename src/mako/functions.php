<?php

namespace mako;

use function filter_var;
use function getenv;
use function json_encode;
use function substr;

/**
 * Builds and returns a "function" used for middleware, route constraints and validation rules.
 *
 * @param  string $_name         Function name
 * @param  mixed  ...$_arguments Function arguments
 * @return string
 */
function f(string $_name, ...$_arguments): string
{
	if(empty($_arguments))
	{
		return $_name;
	}

	return "{$_name}(" . substr(json_encode($_arguments), 1, -1) . ')';
}

/**
 * Returns the value of the chosen environment variable or NULL if it does not exist.
 *
 * @param  string $variableName Variable name
 * @param  mixed  $default      Default value to return if the variable does not exist
 * @param  bool   $isBool       Set to TRUE to treat the value as a boolean
 * @param  bool   $localOnly    Set to TRUE to only return local environment variables
 * @return mixed
 */
function env(string $variableName, mixed $default = null, bool $isBool = false, bool $localOnly = false): mixed
{
	$value = $_ENV[$variableName] ?? (getenv($variableName, $localOnly) ?: null);

	if($isBool && $value !== true && $value !== false && $value !== null)
	{
		$value = filter_var($value, FILTER_VALIDATE_BOOL);
	}

	return $value ?? $default;
}
