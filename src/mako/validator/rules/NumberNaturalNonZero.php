<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use Override;

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
	#[Override]
	public function validate(mixed $value, string $field, array $input): bool
	{
		return is_int($value) && $value > 0;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain a natural non-zero number.', $field);
	}
}
