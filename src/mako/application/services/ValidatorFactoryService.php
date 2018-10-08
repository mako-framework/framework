<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\validator\ValidatorFactory;

/**
 * Validator factory service.
 *
 * @author Frederic G. Østby
 */
class ValidatorFactoryService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->container->registerSingleton([ValidatorFactory::class, 'validator'], function($container)
		{
			return new ValidatorFactory($container->has('i18n') ? $container->get('i18n') : null, $container);
		});
	}
}
