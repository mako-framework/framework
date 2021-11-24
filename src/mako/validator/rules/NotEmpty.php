<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use mako\utility\Arr;
use mako\validator\rules\traits\ValidatesWhenEmptyTrait;

use function in_array;
use function sprintf;

/**
 * Required rule.
 */
class NotEmpty extends Rule implements RuleInterface
{
	use ValidatesWhenEmptyTrait;

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, string $field, array $input): bool
	{
		return !Arr::has($input, $field) || !in_array($value, ['', null, []], true);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field can\'t be empty.', $field);
	}
}
