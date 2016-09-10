<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\application\services\Service;
use mako\session\Session;
use mako\session\stores\Database;
use mako\session\stores\File;
use mako\session\stores\NullStore;
use mako\session\stores\Redis;

/**
 * Session service.
 *
 * @author  Frederic G. Østby
 */
class SessionService extends Service
{
	/**
	 * Returns a database store instance.
	 *
	 * @access  protected
	 * @param   \mako\syringe\Container        $container       IoC container instance
	 * @param   array                          $config          Store configuration
	 * @param   bool|array                     $classWhitelist  Class whitelist
	 * @return  \mako\session\stores\Database
	 */
	protected function getDatabaseStore($container, $config, $classWhitelist)
	{
		return new Database($container->get('database')->connection($config['configuration']), $config['table'], $classWhitelist);
	}

	/**
	 * Returns a file store instance.
	 *
	 * @access  protected
	 * @param   \mako\syringe\Container    $container       IoC container instance
	 * @param   array                      $config          Store configuration
	 * @param   bool|array                 $classWhitelist  Class whitelist
	 * @return  \mako\session\stores\File
	 */
	protected function getFileStore($container, $config, $classWhitelist)
	{
		return new File($container->get('fileSystem'), $config['path'], $classWhitelist);
	}

	/**
	 * Returns a null store instance.
	 *
	 * @access  protected
	 * @param   \mako\syringe\Container         $container       IoC container instance
	 * @param   array                           $config          Store configuration
	 * @param   bool|array                      $classWhitelist  Class whitelist
	 * @return  \mako\session\stores\NullStore
	 */
	protected function getNullStore($container, $config, $classWhitelist)
	{
		return new NullStore;
	}

	/**
	 * Returns a redis store instance.
	 *
	 * @access  protected
	 * @param   \mako\syringe\Container     $container       IoC container instance
	 * @param   array                       $config          Store configuration
	 * @param   bool|array                  $classWhitelist  Class whitelist
	 * @return  \mako\session\stores\Redis
	 */
	protected function getRedisStore($container, $config, $classWhitelist)
	{
		return new Redis($container->get('redis')->connection($config['configuration']), $classWhitelist);
	}

	/**
	 * Returns a session store instance.
	 *
	 * @access  protected
	 * @param   \mako\syringe\Container              $container       IoC container instance
	 * @param   array                                $config          Session configuration
	 * @param   bool|array                           $classWhitelist  Class whitelist
	 * @return  \mako\session\stores\StoreInterface
	 */
	protected function getSessionStore($container, $config, $classWhitelist)
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
	public function register()
	{
		$this->container->registerSingleton([Session::class, 'session'], function($container)
		{
			// Get configuration

			$config = $container->get('config');

			$classWhitelist = $config->get('application.deserialization_whitelist');

			$config = $config->get('session');

			// Get session store instance

			$sessionStore = $this->getSessionStore($container, $config, $classWhitelist);

			// Create session and return it

			$session = new Session($container->get('request'), $container->get('response'), $sessionStore);

			$session->setDataTTL($config['ttl']['data']);

			$session->setCookieTTL($config['ttl']['cookie']);

			$session->setCookieName($config['session_name']);

			$session->setCookieOptions($config['cookie_options']);

			$session->start();

			return $session;
		});
	}
}