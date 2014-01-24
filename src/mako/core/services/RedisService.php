<?php

namespace mako\core\services;

use \mako\redis\ConnectionManager;

/**
 * Redis service.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class RedisService extends \mako\core\services\Service
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
	 * Registers the service.
	 * 
	 * @access  public
	 */

	public function register()
	{
		$this->application->registerSingleton(['mako\redis\ConnectionManager', 'redis'], function($app)
		{
			$config = $app->getConfig()->get('redis');

			return new ConnectionManager($config['default'], $config['configurations']);
		});
	}
}

/** -------------------- End of file -------------------- **/