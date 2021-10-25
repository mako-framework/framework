<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\redis\ConnectionManager;
use mako\redis\Redis;

/**
 * Redis service.
 *
 * @author Frederic G. Østby
 */
class RedisService extends Service
{
	/**
	 * {@inheritDoc}
	 */
	public function register(): void
	{
		$config = $this->config;

		// Register the connection manager

		$this->container->registerSingleton([ConnectionManager::class, 'redis'], static function() use ($config)
		{
			$config = $config->get('redis');

			return new ConnectionManager($config['default'], $config['configurations']);
		});

		// Register the default connection

		$this->container->registerSingleton(Redis::class, static function($container)
		{
			return $container->get(ConnectionManager::class)->connection();
		});
	}
}
