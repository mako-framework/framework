<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\combinators;

use mako\validator\rules\RuleInterface;

/**
 * Rule combinator interface.
 */
interface RuleCombinatorInterface
{
	/**
	 * Get the rules.
	 *
	 * @return (RuleInterface|string)[]
	 */
	public function getRules(): array;

	/**
	 * Returns a callable that can be used to check if the success condition is met.
	 *
	 * @return callable(int): bool
	 */
	public function getSuccessCondition(): callable;

	/**
	 * Returns an error message.
	 */
	public function getErrorMessage(string $field): string;
}
