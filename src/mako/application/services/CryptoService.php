<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\security\crypto\CryptoManager;

/**
 * Crypto service.
 *
 * @author Frederic G. Østby
 */
class CryptoService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		$this->container->registerSingleton([CryptoManager::class, 'crypto'], function($container)
		{
			$config = $this->config->get('crypto');

			return new CryptoManager($config['default'], $config['configurations'], $container);
		});
	}
}
