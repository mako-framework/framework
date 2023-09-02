<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator;

use mako\i18n\I18n;
use mako\syringe\Container;

/**
 * Validator factory.
 */
class ValidatorFactory
{
	/**
	 * Custom rules.
	 */
	protected array $rules = [];

	/**
	 * Constructor.
	 */
	public function __construct(
		protected ?I18n $i18n = null,
		protected ?Container $container = null
	)
	{}

	/**
	 * Registers a custom validation rule.
	 */
	public function extend(string $rule, string $ruleClass): ValidatorFactory
	{
		$this->rules[$rule] = $ruleClass;

		return $this;
	}

	/**
	 * Creates and returns a validator instance.
	 */
	public function create(array $input, array $ruleSets = []): Validator
	{
		$validator = new Validator($input, $ruleSets, $this->i18n, $this->container);

		foreach($this->rules as $rule => $ruleClass)
		{
			$validator->extend($rule, $ruleClass);
		}

		return $validator;
	}
}
