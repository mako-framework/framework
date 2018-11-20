<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\application\Application;
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
	public function register()
	{
		$this->container->registerSingleton([I18n::class, 'i18n'], function($container)
		{
			$app = $container->get(Application::class);

			$cache = $this->config->get('application.language_cache');

			$i18n = new I18n(new Loader($container->get(FileSystem::class), $app->getPath() . '/resources/i18n'), $app->getLanguage());

			if($cache !== false)
			{
				$i18n->setCache($container->get(CacheManager::class)->instance($cache === true ? null : $cache));
			}

			return $i18n;
		});
	}
}
