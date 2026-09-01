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
	 * Returns true if the combinator is considered successful and false if not.
	 */
	public function isSuccessful(int $successes): bool;

    /**
     * Returns true if child rule error messages should be aggregated for this failure and false if not.
     */
    public function shouldAggregateChildErrors(int $successes, array $errorMessages): bool;

	/**
	 * Returns an error message.
	 */
	public function getErrorMessage(string $field): string;
}
