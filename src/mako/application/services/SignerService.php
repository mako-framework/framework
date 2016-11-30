<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\application\services\Service;
use mako\security\Key;
use mako\security\Signer;

/**
 * Signer service.
 *
 * @author  Frederic G. Østby
 */
class SignerService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->container->registerSingleton([Signer::class, 'signer'], function($container)
		{
			return new Signer(Key::decode($container->get('config')->get('application.secret')));
		});
	}
}
