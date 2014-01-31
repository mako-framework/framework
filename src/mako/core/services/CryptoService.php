<?php

namespace mako\core\services;

use \mako\security\crypto\CryptoManager;

/**
 * Crypto service.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class CryptoService extends \mako\core\services\Service
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
		$this->application->registerSingleton(['mako\security\crypto\CryptoManager', 'crypto'], function($app)
		{
			$config = $app->getConfig()->get('crypto');

			return new CryptoManager($config['default'], $config['configurations'], $app);
		});
	}
}

/** -------------------- End of file -------------------- **/