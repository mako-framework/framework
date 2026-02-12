<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\database\ConnectionManager as DatabaseConnectionManager;
use mako\file\FileSystem;
use mako\http\Request;
use mako\http\Response;
use mako\redis\ConnectionManager as RedisConnectionManager;
use mako\session\Session;
use mako\session\stores\APCu;
use mako\session\stores\Database;
use mako\session\stores\File;
use mako\session\stores\NullStore;
use mako\session\stores\Redis;
use mako\session\stores\StoreInterface;
use mako\syringe\Container;
use Override;

/**
 * Session service.
 */
class SessionService extends Service
{
	/**
	 * Default prefix.
	 */
	protected const string DEFAULT_PREFIX = 'mako:cache:';

	/**
	 * Returns an APCu store instance.
	 */
	protected function getApcuStore(Container $container, array $config, array|bool $classWhitelist): APCu
	{
		return new APCu($classWhitelist, $config['prefix'] ?? 'mako:session:');
	}

	/**
	 * Returns a database store instance.
	 */
	protected function getDatabaseStore(Container $container, array $config, array|bool $classWhitelist): Database
	{
		return new Database($container->get(DatabaseConnectionManager::class)->getConnection($config['configuration']), $config['table'], $classWhitelist);
	}

	/**
	 * Returns a file store instance.
	 */
	protected function getFileStore(Container $container, array $config, array|bool $classWhitelist): File
	{
		return new File($container->get(FileSystem::class), $config['path'], $classWhitelist);
	}

	/**
	 * Returns a null store instance.
	 */
	protected function getNullStore(Container $container, array $config, array|bool $classWhitelist): NullStore
	{
		return new NullStore;
	}

	/**
	 * Returns a redis store instance.
	 */
	protected function getRedisStore(Container $container, array $config, array|bool $classWhitelist): Redis
	{
		return new Redis($container->get(RedisConnectionManager::class)->getConnection($config['configuration']), $classWhitelist, $config['prefix'] ?? static::DEFAULT_PREFIX);
	}

	/**
	 * Returns a session store instance.
	 */
	protected function getStore(Container $container, array $config, array|bool $classWhitelist): StoreInterface
	{
		$config = $config['configurations'][$config['configuration']];

		return $this->{"get{$config['type']}Store"}($container, $config, $classWhitelist);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function register(): void
	{
		$this->container->registerSingleton([Session::class, 'session'], function ($container) {
			// Get configuration

			$config = $this->config->get('session');

			$classWhitelist = $this->config->get('application.deserialization_whitelist');

			// Build options array

			$options =
			[
				'name'           => $config['session_name'],
				'data_ttl'       => $config['ttl']['data'],
				'cookie_ttl'     => $config['ttl']['cookie'],
				'cookie_options' => $config['cookie_options'],
			];

			// Create session and return it

			return new Session($container->get(Request::class), $container->get(Response::class), $this->getStore($container, $config, $classWhitelist), $options);
		});
	}
}
