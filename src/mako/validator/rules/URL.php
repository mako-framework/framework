<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use function filter_var;
use function sprintf;

/**
 * URL rule.
 *
 * @author Frederic G. Østby
 */
class URL extends Rule implements RuleInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function validate($value, array $input): bool
	{
		return filter_var($value, FILTER_VALIDATE_URL) !== false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain a valid URL.', $field);
	}
}
