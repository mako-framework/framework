<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\cache\CacheManager;

/**
 * Cache service.
 *
 * @author Frederic G. Østby
 */
class CacheService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		$this->container->registerSingleton([CacheManager::class, 'cache'], function($container)
		{
			// Get configuration

			$classWhitelist = $this->config->get('application.deserialization_whitelist');

			$config = $this->config->get('cache');

			// Create and return cache manager

			return new CacheManager($config['default'], $config['configurations'], $container, $classWhitelist);
		});
	}
}
