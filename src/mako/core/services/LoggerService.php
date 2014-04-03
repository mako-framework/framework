<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\core\services;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;

/**
 * Logger service.
 *
 * @author  Frederic G. Østby
 */

class LoggerService extends \mako\core\services\Service
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
		$this->application->registerSingleton(['Psr\Log\LoggerInterface', 'logger'], function($app)
		{
			$logger = new Logger('mako');

			$logger->pushHandler(new StreamHandler($app->getApplicationPath() . '/storage/logs/' . date('Y-m-d') . '.mako', Logger::DEBUG));

			return $logger;
		});
	}
}

