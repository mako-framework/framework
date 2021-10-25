<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use function preg_match;
use function sprintf;

/**
 * Integer rule.
 *
 * @author Frederic G. Østby
 */
class Integer extends Rule implements RuleInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function validate($value, array $input): bool
	{
		return preg_match('/(^(\-?)0$)|(^(\-?)[1-9]\d*$)/', $value) === 1;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain an integer.', $field);
	}
}
