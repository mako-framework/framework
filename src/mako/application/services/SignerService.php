<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\application\services\Service;
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
		$this->container->registerSingleton(['mako\security\Signer', 'signer'], function($container)
		{
			return new Signer($container->get('config')->get('application.secret'));
		});
	}
}