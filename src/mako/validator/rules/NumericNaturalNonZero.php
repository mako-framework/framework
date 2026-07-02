<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use Override;

use function filter_var;
use function sprintf;

/**
 * Numeric natural non-zero rule.
 */
class NumericNaturalNonZero extends Rule
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function validate(mixed $value, string $field, array $input): bool
	{
		return filter_var($value, FILTER_VALIDATE_INT) !== false && (int) $value > 0;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain a non zero natural number.', $field);
	}
}
