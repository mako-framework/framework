<?php

namespace mako\core\services;

use \mako\http\Response;

/**
 * Response service.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class ResponseService extends \mako\core\services\Service
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
		$this->application->registerSingleton(['mako\http\Response', 'response'], function()
		{
			return new Response($this->application->get('request'), $this->application->get('signer'), $this->application->getCharset());
		});
	}
}

/** -------------------- End of file -------------------- **/