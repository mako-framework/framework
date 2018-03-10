<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use mako\utility\UUID as UUIDGenerator;

/**
 * UUID rule.
 *
 * @author Frederic G. Østby
 */
class UUID extends Rule implements RuleInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return UUIDGenerator::validate($value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain a valid UUID.', $field);
	}
}
