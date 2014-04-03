<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\core\services;

use \mako\validator\ValidatorFactory;
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
		if($this->application->has('session'))
		{
			$validatorFactory->registerPlugin(new TokenValidator($this->application->get('session')));
		}

		if($this->application->has('database'))
		{
			$validatorFactory->registerPlugin(new DatabaseExistsValidator($this->application->get('database')));

			$validatorFactory->registerPlugin(new DatabaseUniqueValidator($this->application->get('database')));
		}
	}
	
	/**
	 * Registers the service.
	 * 
	 * @access  public
	 */

	public function register()
	{
		$this->application->registerSingleton(['mako\validator\ValidatorFactory', 'validatorfactory'], function($app)
		{
			$validatorFactory = new ValidatorFactory($app->get('i18n'));

			// Register plugins

			$this->registerPlugins($validatorFactory);

			// Return validator factory instance

			return $validatorFactory;
		});
	}
}

