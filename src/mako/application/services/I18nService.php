<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\cache\CacheManager;
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
			$i18n = new I18n(new Loader($container->get(FileSystem::class), "{$app->getPath()}/resources/i18n"), $app->getLanguage());

			$cache = $config->get('application.language_cache');

			if($cache !== false)
			{
				$i18n->setCache($container->get(CacheManager::class)->instance($cache === true ? null : $cache));
			}

			return $i18n;
		});
	}
}
