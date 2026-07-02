<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\combinators;

use mako\validator\rules\I18nAwareInterface;
use mako\validator\rules\RuleInterface;
use mako\validator\rules\traits\I18nAwareTrait;
use Override;

/**
 * Base rule combinator.
 */
abstract class RuleCombinator implements I18nAwareInterface, RuleCombinatorInterface
{
	use I18nAwareTrait;

	/**
	 * @var (RuleInterface|string)[]
	 */
	final protected array $rules;

	/**
	 * Constructor.
	 */
	final public function __construct(RuleInterface|string ...$rule)
	{
		$this->rules = $rule;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	final public function getRules(): array
	{
		return $this->rules;
	}
}
