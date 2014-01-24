<?php

namespace mako\core\services;

use \mako\session\Session;
use \mako\session\store\File;

/**
 * Session service.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
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
			case 'file':
				$this->registerFileStore($config);
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
		$config = $this->application->getConfig()->get('session');

		$this->registerSessionStore($config);

		$this->application->registerSingleton(['mako\session\Session', 'session'], function($app) use ($config)
		{
			$session = new Session($app->get('request'), $app->get('response'), $app->get('session.store'));

			$session->setCookieName($config['session_name']);

			$session->setCookieOptions($config['cookie_parameters']);

			$session->start();

			return $session;
		});
	}
}

/** -------------------- End of file -------------------- **/