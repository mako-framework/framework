<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\input;

use mako\validator\Validator;

/**
 * Input.
 */
abstract class Input implements InputInterface
{
	/**
	 * Validation rules.
	 *
	 * @var array
	 */
	protected $rules;

	/**
	 * Error message.
	 *
	 * @var string|null
	 */
	protected $errorMessage;

	/**
	 * Validation extensions.
	 *
	 * @var array
	 */
	protected $extensions = [];

	/**
	 * {@inheritDoc}
	 */
	public function getRules(): array
	{
		return $this->rules;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getExtensions(): array
	{
		return $this->extensions;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addConditionalRules(Validator $validator): void
	{
		// Nothing here
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(): ?string
	{
		return $this->errorMessage;
	}
}
