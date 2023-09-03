<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\input\traits;

use mako\validator\exceptions\ValidationException;
use mako\validator\input\InputInterface;

use function is_string;

/**
 * Input validation trait.
 *
 * @property \mako\syringe\Container          $container
 * @property \mako\validator\ValidatorFactory $validator
 */
trait InputValidationTrait
{
	/**
	 * Validates the input and returns an array containing the validated data.
	 */
	protected function getValidatedInput(array|string $input, array $rules = []): array
	{
		if(is_string($input))
		{
			$input = (fn (string $input): InputInterface => $this->container->get($input))($input);

			$validator = $this->validator->create($input->getInput(), $rules + $input->getRules());

			foreach($input->getExtensions() as $rule => $ruleClass)
			{
				$validator->extend($rule, $ruleClass);
			}

			$input->addConditionalRules($validator);

			try
			{
				return $validator->getValidatedInput();
			}
			catch(ValidationException $e)
			{
				$e->setInput($input);

				throw $e;
			}
		}

		return $this->validator->create($input, $rules)->getValidatedInput();
	}
}
