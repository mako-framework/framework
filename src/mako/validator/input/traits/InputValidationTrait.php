<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\input\traits;

use mako\syringe\Container;
use mako\validator\exceptions\ValidationException;
use mako\validator\input\InputInterface;
use mako\validator\ValidatorFactory;

use function is_string;

/**
 * Input validation trait.
 *
 * @property Container        $container
 * @property ValidatorFactory $validator
 */
trait InputValidationTrait
{
	/**
	 * Validates the input and returns an array containing the validated data.
	 */
	protected function getValidatedInput(array|string $input, array $rules = [], bool $validateEmptyFields = false): array
	{
		if (is_string($input)) {
			$input = (fn (string $input): InputInterface => $this->container->get($input))($input);

			$validator = $this->validator->create($input->getInput(), $rules + $input->getRules(), $validateEmptyFields);

			foreach ($input->getExtensions() as $rule => $ruleClass) {
				$validator->extend($rule, $ruleClass);
			}

			$input->addConditionalRules($validator);

			try {
				return $validator->getValidatedInput();
			}
			catch (ValidationException $e) {
				$e->setInput($input);

				throw $e;
			}
		}

		return $this->validator->create($input, $rules, $validateEmptyFields)->getValidatedInput();
	}
}
