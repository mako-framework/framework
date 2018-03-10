<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use mako\validator\rules\traits\ValidatesWhenEmptyTrait;

/**
 * Required rule.
 *
 * @author Frederic G. Østby
 */
class Required extends Rule implements RuleInterface
{
	use ValidatesWhenEmptyTrait;

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return !in_array($value, ['', null, []], true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field is required.', $field);
	}
}
