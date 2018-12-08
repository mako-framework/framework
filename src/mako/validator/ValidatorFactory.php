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
 *
 * @author Frederic G. Østby
 */
class ValidatorFactory
{
	/**
	 * I18n instance.
	 *
	 * @var \mako\i18n\I18n|null
	 */
	protected $i18n;

	/**
	 * Container.
	 *
	 * @var \mako\syringe\Container|null
	 */
	protected $container;

	/**
	 * Custom rules.
	 *
	 * @var array
	 */
	protected $rules = [];

	/**
	 * Constructor.
	 *
	 * @param \mako\i18n\I18n|null         $i18n      I18n instance
	 * @param \mako\syringe\Container|null $container Container
	 */
	public function __construct(?I18n $i18n = null, ?Container $container = null)
	{
		$this->i18n = $i18n;

		$this->container = $container;
	}

	/**
	 * Registers a custom validation rule.
	 *
	 * @param  string                           $rule      Rule
	 * @param  string                           $ruleClass Rule class
	 * @return \mako\validator\ValidatorFactory
	 */
	public function extend(string $rule, string $ruleClass): ValidatorFactory
	{
		$this->rules[$rule] = $ruleClass;

		return $this;
	}

	/**
	 * Creates and returns a validator instance.
	 *
	 * @param  array                     $input Array to validate
	 * @param  array                     $rules Array of validation rules
	 * @return \mako\validator\Validator
	 */
	public function create(array $input, array $rules): Validator
	{
		$validator = new Validator($input, $rules, $this->i18n, $this->container);

		foreach($this->rules as $rule => $ruleClass)
		{
			$validator->extend($rule, $ruleClass);
		}

		return $validator;
	}
}
