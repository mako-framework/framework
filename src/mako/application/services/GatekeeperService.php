<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\gatekeeper\Authentication;
use mako\gatekeeper\adapters\Session;
use mako\gatekeeper\repositories\group\GroupRepository;
use mako\gatekeeper\repositories\user\UserRepository;

/**
 * Gatekeeper service.
 *
 * @author Frederic G. Østby
 */
class GatekeeperService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->container->registerSingleton([Authentication::class, 'gatekeeper'], function($container)
		{
			$request = $container->get('request');

			$response = $container->get('response');

			$session = $container->get('session');

			$config = $container->get('config')->get('gatekeeper');

			return new Authentication('session', function() use ($request, $response, $session, $config)
			{
				$userRepository = new UserRepository($config['user_model']);

				$userRepository->setIdentifier($config['identifier']);

				$groupRepository = new GroupRepository($config['group_model']);

				$options =
				[
					'auth_key'       => $config['auth_key'],
					'cookie_options' => $config['cookie_options'],
					'throttling'     => $config['throttling'],
				];

				return new Session($userRepository, $groupRepository, $request, $response, $session, $options);
			});
		});
	}
}
