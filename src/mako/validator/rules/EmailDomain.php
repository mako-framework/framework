<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

/**
 * Email domain rule.
 *
 * @author Frederic G. Østby
 */
class EmailDomain extends Rule implements RuleInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		if(empty($value) || strpos($value, '@') === false)
		{
			return false;
		}

		$email = explode('@', $value);

		return checkdnsrr(array_pop($email), 'MX');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain a valid e-mail address.', $field);
	}
}
