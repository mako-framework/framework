<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

/**
 * Email rule.
 *
 * @author Frederic G. Østby
 */
class Email extends Rule implements RuleInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain a valid e-mail address.', $field);
	}
}
