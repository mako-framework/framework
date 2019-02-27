<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\gatekeeper\adapters\Session;
use mako\gatekeeper\Gatekeeper;
use mako\gatekeeper\repositories\group\GroupRepository;
use mako\gatekeeper\repositories\user\UserRepository;
use mako\http\Request;
use mako\http\Response;
use mako\session\Session as HttpSession;

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
	public function register(): void
	{
		$this->container->registerSingleton([Gatekeeper::class, 'gatekeeper'], function($container)
		{
			$request = $container->get(Request::class);

			$response = $container->get(Response::class);

			$session = $container->get(HttpSession::class);

			$config = $this->config->get('gatekeeper');

			return new Gatekeeper('session', function() use ($request, $response, $session, $config)
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
