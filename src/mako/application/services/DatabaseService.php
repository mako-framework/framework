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
use Override;

/**
 * Database service.
 */
class DatabaseService extends Service
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function register(): void
	{
		$config = $this->config;

		// Register the connection manager

		$this->container->registerSingleton([ConnectionManager::class, 'database'], static function ($container) use ($config) {
			if ($container->has(PaginationFactoryInterface::class)) {
				Query::setPaginationFactory(static fn () => $container->get(PaginationFactoryInterface::class));
			}

			$config = $config->get('database');

			return new ConnectionManager($config['default'], $config['configurations']);
		});

		// Register the default connection

		$this->container->registerSingleton(Connection::class, static fn ($container) => $container->get(ConnectionManager::class)->getConnection());
	}
}
