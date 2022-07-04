<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use function filter_var;
use function sprintf;

/**
 * Numeric integer rule.
 */
class NumericInt extends Rule implements RuleInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function validate(mixed $value, string $field, array $input): bool
	{
		return filter_var($value, FILTER_VALIDATE_INT) !== false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain an integer.', $field);
	}
}
