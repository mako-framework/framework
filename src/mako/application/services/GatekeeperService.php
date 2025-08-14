<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\gatekeeper\adapters\Session;
use mako\gatekeeper\authorization\Authorizer;
use mako\gatekeeper\authorization\AuthorizerInterface;
use mako\gatekeeper\Gatekeeper;
use mako\gatekeeper\repositories\group\GroupRepository;
use mako\gatekeeper\repositories\user\UserRepository;
use mako\http\Request;
use mako\http\Response;
use mako\session\Session as HttpSession;
use Override;

/**
 * Gatekeeper service.
 */
class GatekeeperService extends Service
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function register(): void
	{
		$config = $this->config->get('gatekeeper');

		// Register the authorizer

		$this->container->registerSingleton([AuthorizerInterface::class, 'authorizer'], static function ($container) use ($config) {
			$authorizer = new Authorizer($container);

			foreach ($config['policies'] as $entity => $policy) {
				$authorizer->registerPolicy($entity, $policy);
			}

			return $authorizer;
		});

		// Register gatekeeper

		$this->container->registerSingleton([Gatekeeper::class, 'gatekeeper'], static function ($container) use ($config) {
			// Adapter factory

			$factory = static function () use ($container, $config) {
				$userRepository = new UserRepository($config['user_model'], $container->get(AuthorizerInterface::class));

				$userRepository->setIdentifier($config['identifier']);

				$groupRepository = new GroupRepository($config['group_model']);

				$options = [
					'auth_key'       => $config['auth_key'],
					'cookie_options' => $config['cookie_options'],
					'throttling'     => $config['throttling'],
				];

				$request = $container->get(Request::class);

				$response = $container->get(Response::class);

				$session = $container->get(HttpSession::class);

				return new Session($userRepository, $groupRepository, $request, $response, $session, $options);
			};

			// Create and return the gatekeeper instance

			return new Gatekeeper(['session', $factory]);
		});
	}
}
