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
	 */
	public function validateWhenEmpty(): bool;

	/**
	 * Returns TRUE if the rule succeeds and FALSE if not.
	 */
	public function validate(mixed $value, string $field, array $input): bool;

	/**
	 * Returns an error message.
	 */
	public function getErrorMessage(string $field): string;
}
