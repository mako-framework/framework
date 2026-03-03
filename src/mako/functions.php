<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako {

    use mako\env\Type;

	use function filter_var;
	use function getenv;
	use function json_decode;
	use function json_encode;
	use function substr;

	/**
	 * Builds and returns a "function" used for middleware, route constraints and validation rules.
	 */
	function f(string $_name, mixed ...$_arguments): string
	{
		if (empty($_arguments)) {
			return $_name;
		}

		return "{$_name}(" . substr(json_encode($_arguments), 1, -1) . ')';
	}

	/**
	 * Returns the value of the chosen environment variable or NULL if it does not exist.
	 */
	function env(string $variableName, mixed $default = null, bool $localOnly = false, ?Type $as = null): mixed
	{
		$value = $_ENV[$variableName] ?? (getenv($variableName, $localOnly) ?: null);

		return match ($as) {
			null => $value,
			Type::Bool => filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE),
			Type::Int => filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE),
			Type::Float => filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE),
			Type::JsonAsObject => json_decode($value ?? 'null'),
			Type::JsonAsArray => json_decode($value ?? 'null', flags: JSON_OBJECT_AS_ARRAY),
		} ?? $default;
	}
}

namespace mako\syringe {
	use function implode;

	/**
	 * Returns the string representation of the intersection of the provided types.
	 */
	function intersection(string ...$types): string
	{
		return implode('&', $types);
	}
}
