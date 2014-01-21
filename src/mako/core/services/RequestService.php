<?php

namespace mako\core\services;

use \mako\http\Request;

/**
 * Request service.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class RequestService extends \mako\core\services\Service
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
		$this->application->registerSingleton(['mako\http\Request', 'request'], function()
		{
			return new Request(['languages' => $this->application->getConfig()->get('application.languages')], $this->application->get('signer'));
		});
	}
}

/** -------------------- End of file -------------------- **/