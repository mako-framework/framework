<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\database\ConnectionManager;
use mako\database\connections\Connection;
use mako\database\query\Query;
use mako\pagination\PaginationFactoryInterface;

/**
 * Database service.
 */
class DatabaseService extends Service
{
	/**
	 * {@inheritDoc}
	 */
	public function register(): void
	{
		$config = $this->config;

		// Register the connection manager

		$this->container->registerSingleton([ConnectionManager::class, 'database'], static function($container) use ($config)
		{
			if($container->has(PaginationFactoryInterface::class))
			{
				Query::setPaginationFactory(static function() use ($container)
				{
					return $container->get(PaginationFactoryInterface::class);
				});
			}

			$config = $config->get('database');

			return new ConnectionManager($config['default'], $config['configurations']);
		});

		// Register the default connection

		$this->container->registerSingleton(Connection::class, static function($container)
		{
			return $container->get(ConnectionManager::class)->connection();
		});
	}
}
