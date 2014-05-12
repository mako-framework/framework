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
	/**
	 * Registers the service.
	 * 
	 * @access  public
	 */

	public function register()
	{
		$this->container->registerSingleton(['mako\auth\Gatekeeper', 'gatekeeper'], function($container)
		{
			$config = $container->get('config')->get('gatekeeper');

			$gatekeeper = new Gatekeeper($container->get('request'), $container->get('response'), $container->get('session'));

			$gatekeeper->setAuthKey($config['auth_key']);

			$gatekeeper->setUserModel($config['user_model']);

			$gatekeeper->setCookieOptions($config['cookie_options']);

			return $gatekeeper;
		});
	}
}