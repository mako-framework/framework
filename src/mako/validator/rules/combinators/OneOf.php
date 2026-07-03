<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\combinators;

use Override;

use function sprintf;

/**
 * One of rule combinator.
 */
class OneOf extends RuleCombinator
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function isSuccessful(int $successes): bool
	{
		return $successes === 1;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function shouldAggregateChildErrors(int $successes, array $errorMessages): bool
	{
		return !empty($errorMessages) && $successes === 0;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must satisfy exactly one of its validation rules.', $field);
	}
}
