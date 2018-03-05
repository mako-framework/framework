<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\validator\plugins\DatabaseExistsValidator;
use mako\validator\plugins\DatabaseUniqueValidator;
use mako\validator\plugins\OneTimeTokenValidator;
use mako\validator\plugins\TokenValidator;
use mako\validator\ValidatorFactory;

/**
 * Validator factory service.
 *
 * @author Frederic G. Østby
 */
class ValidatorFactoryService extends Service
{
	/**
	 * Registers plugins.
	 *
	 * @param \mako\validator\ValidatorFactory $validatorFactory Validator factory instance
	 */
	protected function registerPlugins(ValidatorFactory $validatorFactory)
	{
		if($this->container->has('session'))
		{
			$validatorFactory->registerPlugin(new OneTimeTokenValidator($this->container->get('session')));

			$validatorFactory->registerPlugin(new TokenValidator($this->container->get('session')));
		}

		if($this->container->has('database'))
		{
			$validatorFactory->registerPlugin(new DatabaseExistsValidator($this->container->get('database')));

			$validatorFactory->registerPlugin(new DatabaseUniqueValidator($this->container->get('database')));
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->container->registerSingleton([ValidatorFactory::class, 'validator'], function($container)
		{
			$validatorFactory = new ValidatorFactory($container->get('i18n'));

			// Register plugins

			$this->registerPlugins($validatorFactory);

			// Return validator factory instance

			return $validatorFactory;
		});
	}
}
