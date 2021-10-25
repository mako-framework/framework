<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing\traits;

use mako\validator\input\traits\InputValidationTrait as BaseInputValidationTrait;

use function is_string;

/**
 * Input validation trait.
 *
 * @author Frederic G. Østby
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
	 *
	 * @param  array|string $inputOrRules Input class name or an array of validation rules
	 * @return array
	 */
	protected function getValidatedInput($inputOrRules): array
	{
		if(is_string($inputOrRules))
		{
			return $this->baseGetValidatedInput($inputOrRules);
		}

		return $this->baseGetValidatedInput($this->request->getData()->all(), $inputOrRules);
	}

	/**
	 * Returns an array containing validated request files.
	 *
	 * @param  array|string $inputOrRules Input class name or an array of validation rules
	 * @return array
	 */
	protected function getValidatedFiles($inputOrRules): array
	{
		if(is_string($inputOrRules))
		{
			return $this->baseGetValidatedInput($inputOrRules);
		}

		return $this->baseGetValidatedInput($this->request->getFiles()->all(), $inputOrRules);
	}
}
