<?php

namespace mako;

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
