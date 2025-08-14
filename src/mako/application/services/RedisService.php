<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\redis\ConnectionManager;
use mako\redis\Redis;
use Override;

/**
 * Redis service.
 */
class RedisService extends Service
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function register(): void
	{
		$config = $this->config;

		// Register the connection manager

		$this->container->registerSingleton([ConnectionManager::class, 'redis'], static function () use ($config) {
			$config = $config->get('redis');

			return new ConnectionManager($config['default'], $config['configurations']);
		});

		// Register the default connection

		$this->container->registerSingleton(Redis::class, static fn ($container) => $container->get(ConnectionManager::class)->getConnection());
	}
}
