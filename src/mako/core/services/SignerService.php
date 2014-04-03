<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\core\services;

use \mako\security\Signer;

/**
 * Signer service.
 *
 * @author  Frederic G. Østby
 */

class SignerService extends \mako\core\services\Service
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
		$this->application->registerSingleton(['mako\security\Signer', 'signer'], function($app)
		{
			return new Signer($app->getConfig()->get('application.secret'));
		});
	}
}