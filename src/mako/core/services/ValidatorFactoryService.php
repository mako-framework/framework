<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\core\services;

use \mako\validator\ValidatorFactory;
use \mako\validator\plugins\ConfigExistsValidator;
use \mako\validator\plugins\ConfigKeyExistsValidator;
use \mako\validator\plugins\DatabaseExistsValidator;
use \mako\validator\plugins\DatabaseUniqueValidator;
use \mako\validator\plugins\TokenValidator;

/**
 * Validator factory service.
 *
 * @author  Frederic G. Østby
 */

class ValidatorFactoryService extends \mako\core\services\Service
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Registers plugins.
	 *
	 * @access  protected
	 * @param   \mako\validator\ValidatorFactory  $validatorFactory  Validator factory instance
	 */

	protected function registerPlugins(ValidatorFactory $validatorFactory)
	{
		if($this->container->has('session'))
		{
			$validatorFactory->registerPlugin(new TokenValidator($this->container->get('session')));
		}

		if($this->container->has('database'))
		{
			$validatorFactory->registerPlugin(new DatabaseExistsValidator($this->container->get('database')));

			$validatorFactory->registerPlugin(new DatabaseUniqueValidator($this->container->get('database')));
		}

		if($this->container->has('config'))
		{
			$validatorFactory->registerPlugin(new ConfigExistsValidator($this->container->get('config')));

			$validatorFactory->registerPlugin(new ConfigKeyExistsValidator($this->container->get('config')));
		}
	}

	/**
	 * Registers the service.
	 *
	 * @access  public
	 */

	public function register()
	{
		$this->container->registerSingleton(['mako\validator\ValidatorFactory', 'validator'], function($container)
		{
			$validatorFactory = new ValidatorFactory($container->get('i18n'));

			// Register plugins

			$this->registerPlugins($validatorFactory);

			// Return validator factory instance

			return $validatorFactory;
		});
	}
}