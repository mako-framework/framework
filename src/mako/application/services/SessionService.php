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
use mako\session\stores\Redis;
use mako\session\stores\Void;

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
	 * @param   \mako\syringe\Container        $container  IoC container instance
	 * @param   array                          $config     Store configuration
	 * @return  \mako\session\stores\Database
	 */

	protected function getDatabaseStore($container, $config)
	{
		return new Database($container->get('database')->connection($config['configuration']), $config['table']);
	}

	/**
	 * Returns a file store instance.
	 *
	 * @access  protected
	 * @param   \mako\syringe\Container    $container  IoC container instance
	 * @param   array                      $config     Store configuration
	 * @return  \mako\session\stores\File
	 */

	protected function getFileStore($container, $config)
	{
		return new File($container->get('fileSystem'), $config['path']);
	}

	/**
	 * Returns a redis store instance.
	 *
	 * @access  protected
	 * @param   \mako\syringe\Container    $container  IoC container instance
	 * @param   array                      $config     Store configuration
	 * @return  \mako\session\stores\Redis
	 */

	protected function getRedisStore($container, $config)
	{
		return new Redis($container->get('redis')->connection($config['configuration']));
	}

	/**
	 * Returns a void store instance.
	 *
	 * @access  protected
	 * @param   \mako\syringe\Container    $container  IoC container instance
	 * @param   array                      $config     Store configuration
	 * @return  \mako\session\stores\Void
	 */

	protected function getVoidStore($container, $config)
	{
		return new Void;
	}

	/**
	 * Returns a session store instance.
	 *
	 * @access  protected
	 * @param   \mako\syringe\Container              $container  IoC container instance
	 * @param   array                                $config     Session configuration
	 * @return  \mako\session\stores\StoreInterface
	 */

	protected function getSessionStore($container, $config)
	{
		$config = $config['configurations'][$config['configuration']];

		switch($config['type'])
		{
			case 'database':
				return $this->getDatabaseStore($container, $config);
				break;
			case 'file':
				return $this->getFileStore($container, $config);
				break;
			case 'null':
				return $this->getNullStore($container, $config);
				break;
			case 'redis':
				return $this->getRedisStore($container, $config);
				break;
		}
	}

	/**
	 * {@inheritdoc}
	 */

	public function register()
	{
		$this->container->registerSingleton(['mako\session\Session', 'session'], function($container)
		{
			$config = $container->get('config')->get('session');

			$session = new Session($container->get('request'), $container->get('response'), $this->getSessionStore($container, $config));

			$session->setDataTTL($config['ttl']['data']);

			$session->setCookieTTL($config['ttl']['cookie']);

			$session->setCookieName($config['session_name']);

			$session->setCookieOptions($config['cookie_options']);

			$session->start();

			return $session;
		});
	}
}