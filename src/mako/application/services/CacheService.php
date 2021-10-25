<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\cache\CacheManager;
use mako\cache\stores\StoreInterface;

/**
 * Cache service.
 *
 * @author Frederic G. Østby
 */
class CacheService extends Service
{
	/**
	 * {@inheritDoc}
	 */
	public function register(): void
	{
		// Register the cache manager

		$this->container->registerSingleton([CacheManager::class, 'cache'], function($container)
		{
			// Get configuration

			$classWhitelist = $this->config->get('application.deserialization_whitelist');

			$config = $this->config->get('cache');

			// Create and return cache manager

			return new CacheManager($config['default'], $config['configurations'], $container, $classWhitelist);
		});

		// Register the default cache store

		$this->container->registerSingleton(StoreInterface::class, static function($container)
		{
			return $container->get(CacheManager::class)->instance();
		});
	}
}
