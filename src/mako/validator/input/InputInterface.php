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
	 *
	 * @return array
	 */
	public function getInput(): array;

	/**
	 * Returns the validation rules.
	 *
	 * @return array
	 */
	public function getRules(): array;

	/**
	 * Returns an array of validator extensions.
	 *
	 * @return array
	 */
	public function getExtensions(): array;

	/**
	 * Adds conditional rules to the validator.
	 *
	 * @param \mako\validator\Validator $validator Validator
	 */
	public function addConditionalRules(Validator $validator): void;

	/**
	 * Returns the error message.
	 *
	 * @return string|null
	 */
	public function getErrorMessage(): ?string;
}
