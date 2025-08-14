<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\security\crypto\CryptoManager;
use Override;

/**
 * Crypto service.
 */
class CryptoService extends Service
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function register(): void
	{
		$config = $this->config;

		// Register the crypto manager

		$this->container->registerSingleton([CryptoManager::class, 'crypto'], static function ($container) use ($config) {
			$config = $config->get('crypto');

			return new CryptoManager($config['default'], $config['configurations'], $container);
		});
	}
}
