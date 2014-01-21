<?php

namespace mako\core\services;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;

/**
 * Logger service.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
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
		$this->application->registerSingleton(['Psr\Log\LoggerInterface', 'logger'], function()
		{
			$logger = new Logger('mako');

			$logger->pushHandler(new StreamHandler($this->application->getApplicationPath() . '/storage/logs/' . date('Y-m-d') . '.mako', Logger::DEBUG));

			return $logger;
		});
	}
}

/** -------------------- End of file -------------------- **/