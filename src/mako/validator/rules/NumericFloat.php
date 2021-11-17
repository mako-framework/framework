<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use function filter_var;
use function preg_match;
use function sprintf;

/**
 * Numberic float rule.
 */
class NumericFloat extends Rule implements RuleInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function validate($value, array $input): bool
	{
		return filter_var($value, FILTER_VALIDATE_FLOAT) !== false && preg_match('/(^(\-?)0$)|(^(\-?)[1-9]\d*$)/', $value) === 0;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain a float.', $field);
	}
}
