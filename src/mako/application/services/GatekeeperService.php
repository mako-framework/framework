<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\application\services\Service;
use mako\auth\Gatekeeper;
use mako\auth\providers\GroupProvider;
use mako\auth\providers\UserProvider;

/**
 * Gatekeeper service.
 *
 * @author  Frederic G. Østby
 */

class GatekeeperService extends Service
{
	/**
	 * {@inheritdoc}
	 */

	public function register()
	{
		$this->container->registerSingleton(['mako\auth\Gatekeeper', 'gatekeeper'], function($container)
		{
			$config = $container->get('config')->get('gatekeeper');

			$userProvider = new UserProvider($config['user_model']);

			$groupProvider = new GroupProvider($config['group_model']);

			$options =
			[
				'identifier' => $config['identifier'],
				'auth_key'   => $config['auth_key'],
				'cookie'     => $config['cookie_options'],
				'throttling' => $config['throttling'],
			];

			return new Gatekeeper($container->get('request'), $container->get('response'), $container->get('session'), $userProvider, $groupProvider, $options);
		});
	}
}