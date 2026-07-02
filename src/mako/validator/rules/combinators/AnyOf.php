<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\combinators;

use Override;

/**
 * Any of rule combinator.
 */
class AnyOf extends RuleCombinator
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getMatchCondition(): callable
	{
		return fn (int $matches): bool => $matches >= 1;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must satisfy at least one of its validation rules.', $field);
	}
}
