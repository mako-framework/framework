<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use function filter_var;
use function sprintf;

/**
 * Numeric natural rule.
 */
class NumericNatural extends Rule implements RuleInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function validate(mixed $value, string $field, array $input): bool
	{
		return filter_var($value, FILTER_VALIDATE_INT) !== false && (int) $value >= 0;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain a natural number.', $field);
	}
}
