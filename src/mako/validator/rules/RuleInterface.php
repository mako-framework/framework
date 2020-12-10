<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

/**
 * Rule interface.
 */
interface RuleInterface
{
	/**
	 * Returns TRUE if the rule should be executed when the input is empty and FALSE if not.
	 *
	 * @return bool
	 */
	public function validateWhenEmpty(): bool;

	/**
	 * Returns TRUE if the rule succeeds and FALSE if not.
	 *
	 * @param  mixed $value Value to validate
	 * @param  array $input Input
	 * @return bool
	 */
	public function validate($value, array $input): bool;

	/**
	 * Returns an error message.
	 *
	 * @param  string $field Field name
	 * @return string
	 */
	public function getErrorMessage(string $field): string;
}
