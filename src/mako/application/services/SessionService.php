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
use mako\session\stores\Database;
use mako\session\stores\File;
use mako\session\stores\NullStore;
use mako\session\stores\Redis;
use mako\syringe\Container;

/**
 * Session service.
 *
 * @author Frederic G. Østby
 */
class SessionService extends Service
{
	/**
	 * Returns a database store instance.
	 *
	 * @param  \mako\syringe\Container       $container      Container
	 * @param  array                         $config         Store configuration
	 * @param  bool|array                    $classWhitelist Class whitelist
	 * @return \mako\session\stores\Database
	 */
	protected function getDatabaseStore(Container $container, array $config, $classWhitelist)
	{
		return new Database($container->get(DatabaseConnectionManager::class)->connection($config['configuration']), $config['table'], $classWhitelist);
	}

	/**
	 * Returns a file store instance.
	 *
	 * @param  \mako\syringe\Container   $container      Container
	 * @param  array                     $config         Store configuration
	 * @param  bool|array                $classWhitelist Class whitelist
	 * @return \mako\session\stores\File
	 */
	protected function getFileStore(Container $container, array $config, $classWhitelist)
	{
		return new File($container->get(FileSystem::class), $config['path'], $classWhitelist);
	}

	/**
	 * Returns a null store instance.
	 *
	 * @param  \mako\syringe\Container        $container      Container
	 * @param  array                          $config         Store configuration
	 * @param  bool|array                     $classWhitelist Class whitelist
	 * @return \mako\session\stores\NullStore
	 */
	protected function getNullStore(Container $container, array $config, $classWhitelist)
	{
		return new NullStore;
	}

	/**
	 * Returns a redis store instance.
	 *
	 * @param  \mako\syringe\Container    $container      Container
	 * @param  array                      $config         Store configuration
	 * @param  bool|array                 $classWhitelist Class whitelist
	 * @return \mako\session\stores\Redis
	 */
	protected function getRedisStore(Container $container, array $config, $classWhitelist)
	{
		return new Redis($container->get(RedisConnectionManager::class)->connection($config['configuration']), $classWhitelist);
	}

	/**
	 * Returns a session store instance.
	 *
	 * @param  \mako\syringe\Container             $container      Container
	 * @param  array                               $config         Session configuration
	 * @param  bool|array                          $classWhitelist Class whitelist
	 * @return \mako\session\stores\StoreInterface
	 */
	protected function getStore(Container $container, array $config, $classWhitelist)
	{
		$config = $config['configurations'][$config['configuration']];

		switch($config['type'])
		{
			case 'database':
				return $this->getDatabaseStore($container, $config, $classWhitelist);
				break;
			case 'file':
				return $this->getFileStore($container, $config, $classWhitelist);
				break;
			case 'null':
				return $this->getNullStore($container, $config, $classWhitelist);
				break;
			case 'redis':
				return $this->getRedisStore($container, $config, $classWhitelist);
				break;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		$this->container->registerSingleton([Session::class, 'session'], function($container)
		{
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
