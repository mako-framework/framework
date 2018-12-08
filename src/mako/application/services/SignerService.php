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
 *
 * @author Frederic G. Østby
 */
class SignerService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		$this->container->registerSingleton([Signer::class, 'signer'], function()
		{
			return new Signer(Key::decode($this->config->get('application.secret')));
		});
	}
}
