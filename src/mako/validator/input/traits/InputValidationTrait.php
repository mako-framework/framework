<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\input\traits;

use mako\validator\input\InputInterface;
use mako\validator\ValidationException;

use function is_string;

/**
 * Input validation trait.
 *
 * @author Frederic G. Østby
 */
trait InputValidationTrait
{
	/**
	 * Validates the input and returns an array containing the validated data.
	 *
	 * @param  string|array $input Input class name or input array
	 * @param  array|null   $rules Validation rules
	 * @return array
	 */
	protected function validate($input, ?array $rules = null): array
	{
		if(is_string($input))
		{
			$input = (function(string $input): InputInterface
			{
				return $this->container->get($input);
			})($input);

			$validator = $this->validator->create($input->getInput(), $input->getRules());

			foreach($input->getExtensions() as $rule => $ruleClass)
			{
				$validator->extend($rule, $ruleClass);
			}

			$input->addConditionalRules($validator);

			try
			{
				return $validator->validate();
			}
			catch(ValidationException $e)
			{
				foreach($input->getMeta() as $key => $value)
				{
					$e->addMeta($key, $value);
				}

				throw $e;
			}
		}

		return $this->validator->create($input, $rules)->validate();
	}
}
