<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use function json_decode;
use function json_last_error;
use function sprintf;

/**
 * JSON rule.
 */
class JSON extends Rule implements RuleInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function validate($value, string $field, array $input): bool
	{
		return (json_decode($value) === null && json_last_error() !== JSON_ERROR_NONE) === false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain valid JSON.', $field);
	}
}
