<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\input;

use mako\validator\Validator;

/**
 * Input interface.
 */
interface InputInterface
{
	/**
	 * Returns the input to validate.
	 */
	public function getInput(): array;

	/**
	 * Returns the validation rules.
	 */
	public function getRules(): array;

	/**
	 * Returns an array of validator extensions.
	 */
	public function getExtensions(): array;

	/**
	 * Adds conditional rules to the validator.
	 */
	public function addConditionalRules(Validator $validator): void;

	/**
	 * Returns the error message.
	 */
	public function getErrorMessage(): ?string;
}
