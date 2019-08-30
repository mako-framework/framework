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
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		// Register the connection manager

		$this->container->registerSingleton([ConnectionManager::class, 'redis'], function()
		{
			$config = $this->config->get('redis');

			return new ConnectionManager($config['default'], $config['configurations']);
		});

		// Register the default connection

		$this->container->registerSingleton(Redis::class, static function ($container)
		{
			return $container->get(ConnectionManager::class)->connection();
		});
	}
}
