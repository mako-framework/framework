<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\core\services;

use \mako\auth\Gatekeeper;

/**
 * Gatekeeper service.
 *
 * @author  Frederic G. Østby
 */

class GatekeeperService extends \mako\core\services\Service
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
		$this->application->registerSingleton(['mako\auth\Gatekeeper', 'gatekeeper'], function($app)
		{
			$config = $app->getConfig()->get('gatekeeper');

			$gatekeeper = new Gatekeeper($app->get('request'), $app->get('response'), $app->get('session'));

			$gatekeeper->setAuthKey($config['auth_key']);

			$gatekeeper->setUserModel($config['user_model']);

			$gatekeeper->setCookieOptions($config['cookie_options']);

			return $gatekeeper;
		});
	}
}