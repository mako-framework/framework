<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use function is_int;
use function sprintf;

/**
 * Number natural non-zero rule.
 */
class NumberNaturalNonZero extends Rule implements RuleInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function validate($value, string $field, array $input): bool
	{
		return is_int($value) && $value > 0;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain a natural non-zero number.', $field);
	}
}
