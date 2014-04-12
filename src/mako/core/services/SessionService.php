<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\core\services;

use \mako\session\Session;
use \mako\session\store\Database;
use \mako\session\store\File;
use \mako\session\store\Redis;

/**
 * Session service.
 *
 * @author  Frederic G. Østby
 */

class SessionService extends \mako\core\services\Service
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Register the database session store.
	 * 
	 * @access  protected
	 * @param   array      $config  Store configuration
	 */

	protected function registerDatabaseStore($config)
	{
		$this->container->register(['mako\session\store\StoreInterface', 'session.store'], function($container) use ($config)
		{
			return new Database($container->get('database')->connection($config['configuration']), $config['table']);
		});
	}

	/**
	 * Retister the file session store.
	 * 
	 * @access  protected
	 * @param   array      $config  Store configuration
	 */

	protected function registerFileStore($config)
	{
		$this->container->register(['mako\session\store\StoreInterface', 'session.store'], function($container) use ($config)
		{
			return new File($config['path']);
		});
	}

	/**
	 * Register the redis session store.
	 * 
	 * @access  protected
	 * @param   array      $config  Store configuration
	 */

	protected function registerRedisStore($config)
	{
		$this->container->register(['mako\session\store\StoreInterface', 'session.store'], function($container) use ($config)
		{
			return new Redis($container->get('redis')->connection($config['configuration']));
		});
	}

	/**
	 * Register the session store.
	 * 
	 * @access  protected
	 * @param   array      $config  Session configuration
	 */

	protected function registerSessionStore($config)
	{
		$config = $config['configurations'][$config['default']];

		switch($config['type'])
		{
			case 'database':
				$this->registerDatabaseStore($config);
				break;
			case 'file':
				$this->registerFileStore($config);
				break;
			case 'redis':
				$this->registerRedisStore($config);
				break;
		}
	}
	
	/**
	 * Registers the service.
	 * 
	 * @access  public
	 */

	public function register()
	{
		$this->container->registerSingleton(['mako\session\Session', 'session'], function($container)
		{
			$config = $container->get('config')->get('session');

			$this->registerSessionStore($config);

			$session = new Session($container->get('request'), $container->get('response'), $container->get('session.store'));

			$session->setCookieName($config['session_name']);

			$session->setCookieOptions($config['cookie_options']);

			$session->start();

			return $session;
		});
	}
}