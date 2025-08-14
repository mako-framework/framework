<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\input;

use mako\validator\Validator;
use Override;

/**
 * Input.
 */
abstract class Input implements InputInterface
{
	/**
	 * Validation rules.
	 */
	protected array $rules = [];

	/**
	 * Error message.
	 */
	protected ?string $errorMessage = null;

	/**
	 * Validation extensions.
	 */
	protected array $extensions = [];

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getRules(): array
	{
		return $this->rules;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getExtensions(): array
	{
		return $this->extensions;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function addConditionalRules(Validator $validator): void
	{
		// Nothing here
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(): ?string
	{
		return $this->errorMessage;
	}
}
