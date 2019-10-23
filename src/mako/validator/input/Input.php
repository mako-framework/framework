<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\input;

use mako\validator\Validator;

/**
 * Input.
 *
 * @author Frederic G. Østby
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
	 * {@inheritdoc}
	 */
	public function getRules(): array
	{
		return $this->rules;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getExtensions(): array
	{
		return $this->extensions;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addConditionalRules(Validator $validator): void
	{
		// Nothing here
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(): ?string
	{
		return $this->errorMessage;
	}
}
