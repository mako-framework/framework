<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

/**
 * JSON rule.
 *
 * @author Frederic G. Østby
 */
class JSON extends Rule implements RuleInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return (json_decode($value) === null && json_last_error() !== JSON_ERROR_NONE) === false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain valid JSON.', $field);
	}
}
