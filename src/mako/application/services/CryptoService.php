<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\application\services\Service;
use mako\security\crypto\CryptoManager;

/**
 * Crypto service.
 *
 * @author  Frederic G. Østby
 */

class CryptoService extends Service
{
	/**
	 * {@inheritdoc}
	 */

	public function register()
	{
		$this->container->registerSingleton(['mako\security\crypto\CryptoManager', 'crypto'], function($container)
		{
			$config = $container->get('config')->get('crypto');

			return new CryptoManager($config['default'], $config['configurations'], $container);
		});
	}
}