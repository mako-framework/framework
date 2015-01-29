<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\validator;

use mako\i18n\I18n;
use mako\validator\Validator;
use mako\validator\plugins\ValidatorPluginInterface;

/**
 * Validator factory.
 *
 * @author  Frederic G. Østby
 */

class ValidatorFactory
{
	/**
	 * I18n instance.
	 *
	 * @var \mako\i18n\I18n
	 */

	protected $i18n;

	/**
	 * Array of registered plugins.
	 *
	 * @var array
	 */

	protected $plugins = [];

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\i18n\I18n  $i18n  I18n instance
	 */

	public function __construct(I18n $i18n)
	{
		$this->i18n = $i18n;
	}

	/**
	 * Creates and returns a validator instance.
	 *
	 * @access  public
	 * @param   array                      $data   Array to validate
	 * @param   array                      $rules  Array of validation rules
	 * @return  \mako\validator\Validator
	 */

	public function create(array $data, array $rules)
	{
		$validator = new Validator($data, $rules, $this->i18n);

		foreach($this->plugins as $plugin)
		{
			$validator->registerPlugin($plugin);
		}

		return $validator;
	}

	/**
	 * Register a validation plugin.
	 *
	 * @access  public
	 * @param   \mako\validator\plugins\ValidatorPluginInterface  $plugin  Plugin instance
	 */

	public function registerPlugin(ValidatorPluginInterface $plugin)
	{
		$this->plugins[] = $plugin;
	}
}