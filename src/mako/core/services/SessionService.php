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
		$this->application->register(['mako\session\store\StoreInterface', 'session.store'], function($app) use ($config)
		{
			return new Database($app->get('database')->connection($config['configuration']), $config['table']);
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
		$this->application->register(['mako\session\store\StoreInterface', 'session.store'], function() use ($config)
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
		$this->application->register(['mako\session\store\StoreInterface', 'session.store'], function($app) use ($config)
		{
			return new Redis($app->get('redis')->connection($config['configuration']));
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
		$this->application->registerSingleton(['mako\session\Session', 'session'], function($app)
		{
			$config = $this->application->getConfig()->get('session');

			$this->registerSessionStore($config);

			$session = new Session($app->get('request'), $app->get('response'), $app->get('session.store'));

			$session->setCookieName($config['session_name']);

			$session->setCookieOptions($config['cookie_options']);

			$session->start();

			return $session;
		});
	}
}

