<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\session;

use mako\session\Session;
use mako\validator\rules\Rule;
use mako\validator\rules\RuleInterface;
use mako\validator\rules\traits\ValidatesWhenEmptyTrait;
use Override;
use SensitiveParameter;

/**
 * One-time token rule.
 */
class OneTimeToken extends Rule implements RuleInterface
{
	use ValidatesWhenEmptyTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Session $session
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function validate(#[SensitiveParameter] mixed $value, string $field, array $input): bool
	{
		return $this->session->validateOneTimeToken($value);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return 'Invalid security token.';
	}
}
