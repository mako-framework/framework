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
 *
 * @author Frederic G. Østby
 */
class I18nService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		$this->container->registerSingleton([I18n::class, 'i18n'], function($container)
		{
			$cache = $this->config->get('application.language_cache');

			$i18n = new I18n(new Loader($container->get(FileSystem::class), "{$this->app->getPath()}/resources/i18n"), $this->app->getLanguage());

			if($cache !== false)
			{
				$i18n->setCache($container->get(CacheManager::class)->instance($cache === true ? null : $cache));
			}

			return $i18n;
		});
	}
}
