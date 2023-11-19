<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\input\http\routing\traits;

use mako\validator\input\traits\InputValidationTrait as BaseInputValidationTrait;

use function is_string;

/**
 * Input validation trait.
 *
 * @property \mako\http\Request $request
 */
trait InputValidationTrait
{
	use BaseInputValidationTrait {
		BaseInputValidationTrait::getValidatedInput as baseGetValidatedInput;
	}

	/**
	 * Returns an array containing validated request input.
	 */
	protected function getValidatedInput(array|string $inputOrRules, bool $validateEmptyFields = false): array
	{
		if (is_string($inputOrRules)) {
			return $this->baseGetValidatedInput($inputOrRules, validateEmptyFields: $validateEmptyFields);
		}

		return $this->baseGetValidatedInput($this->request->getData()->all(), $inputOrRules, $validateEmptyFields);
	}

	/**
	 * Returns an array containing validated request files.
	 */
	protected function getValidatedFiles(array|string $inputOrRules): array
	{
		if (is_string($inputOrRules)) {
			return $this->baseGetValidatedInput($inputOrRules);
		}

		return $this->baseGetValidatedInput($this->request->files->all(), $inputOrRules);
	}
}
