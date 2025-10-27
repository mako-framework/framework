<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\cache\CacheManager;
use mako\cache\simple\SimpleCache;
use mako\cache\stores\StoreInterface;
use Override;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;

/**
 * Cache service.
 */
class CacheService extends Service
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function register(): void
	{
		$config = $this->config;

		// Register the cache manager

		$this->container->registerSingleton([CacheManager::class, 'cache'], static function ($container) use ($config) {
			// Get configuration

			$classWhitelist = $config->get('application.deserialization_whitelist');

			$config = $config->get('cache');

			// Create and return cache manager

			return new CacheManager($config['default'], $config['configurations'], $container, $classWhitelist);
		});

		// Register the default cache store

		$this->container->registerSingleton(StoreInterface::class, static fn ($container) => $container->get(CacheManager::class)->getInstance());

		// Register simple cache with the default cache store

		$this->container->registerSingleton(SimpleCacheInterface::class, static fn ($container) => new SimpleCache($container->get(CacheManager::class)->getInstance()));
	}
}
