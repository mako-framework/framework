<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\security\Key;
use mako\security\Signer;

/**
 * Signer service.
 */
class SignerService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		$config = $this->config;

		// Register the Signer class

		$this->container->registerSingleton([Signer::class, 'signer'], static function() use ($config)
		{
			return new Signer(Key::decode($config->get('application.secret')));
		});
	}
}
