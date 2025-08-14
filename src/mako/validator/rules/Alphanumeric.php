<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use Override;

use function preg_match;
use function sprintf;

/**
 * Alphanumeric rule.
 */
class Alphanumeric extends Rule implements RuleInterface
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function validate(mixed $value, string $field, array $input): bool
	{
		return preg_match('/^[a-z0-9]+$/i', $value) === 1;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain only letters and numbers.', $field);
	}
}
