<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\file\FileSystem;
use mako\i18n\I18n;
use mako\i18n\loaders\Loader;

/**
 * I18n service.
 */
class I18nService extends Service
{
	/**
	 * {@inheritDoc}
	 */
	public function register(): void
	{
		$app = $this->app;

		$config = $this->config;

		// Register the I18n class

		$this->container->registerSingleton([I18n::class, 'i18n'], static function($container) use ($config, $app)
		{
			return new I18n(new Loader($container->get(FileSystem::class), "{$app->getPath()}/resources/i18n"), $app->getLanguage());
		});
	}
}
