<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\core\services;

use \mako\database\ConnectionManager;

/**
 * Database service.
 *
 * @author  Frederic G. Østby
 */

class DatabaseService extends \mako\core\services\Service
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
		$this->application->registerSingleton(['mako\database\ConnectionManager', 'database'], function($app)
		{
			$config = $app->getConfig()->get('database');

			return new ConnectionManager($config['default'], $config['configurations']);
		});
	}
}

