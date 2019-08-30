<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\i18n\I18n;
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
	public function register(): void
	{
		$this->container->registerSingleton([ValidatorFactory::class, 'validator'], static function($container)
		{
			return new ValidatorFactory($container->has(I18n::class) ? $container->get(I18n::class) : null, $container);
		});
	}
}
