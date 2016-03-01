<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\application\services\Service;
use mako\cache\CacheManager;

/**
 * Cache service.
 *
 * @author  Frederic G. Østby
 */
class CacheService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->container->registerSingleton([CacheManager::class, 'cache'], function($container)
		{
			// Get configuration

			$config = $container->get('config');

			$classWhitelist = $config->get('application.deserialization_whitelist');

			$config = $config->get('cache');

			// Create and return cache manager

			return new CacheManager($config['default'], $config['configurations'], $container, $classWhitelist);
		});
	}
}