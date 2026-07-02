<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\combinators;

use Override;

use function count;
use function sprintf;

/**
 * All of rule combinator.
 */
class AllOf extends RuleCombinator
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getMatchCondition(): callable
	{
		return fn (int $matches): bool => count($this->rules) === $matches;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must satisfy all of its validation rules.', $field);
	}
}
