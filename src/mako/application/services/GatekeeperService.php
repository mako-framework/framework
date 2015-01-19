<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\services;

use \mako\auth\Gatekeeper;
use \mako\auth\providers\GroupProvider;
use \mako\auth\providers\UserProvider;

/**
 * Gatekeeper service.
 *
 * @author  Frederic G. Østby
 */

class GatekeeperService extends \mako\application\services\Service
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

			$userProvider = new UserProvider($config['user_model']);

			$groupProvider = new GroupProvider($config['group_model']);

			$gatekeeper = new Gatekeeper($container->get('request'), $container->get('response'), $container->get('session'), $userProvider, $groupProvider);

			$gatekeeper->setAuthKey($config['auth_key']);

			$gatekeeper->setCookieOptions($config['cookie_options']);

			return $gatekeeper;
		});
	}
}