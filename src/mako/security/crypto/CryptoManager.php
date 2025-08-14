<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security\crypto;

use mako\common\AdapterManager;
use mako\security\crypto\encrypters\EncrypterInterface;
use mako\security\crypto\encrypters\OpenSSL;
use mako\security\crypto\exceptions\CryptoException;
use mako\security\Key;
use mako\security\Signer;
use Override;

use function sprintf;

/**
 * Crypto manager.
 *
 * @mixin \mako\security\crypto\encrypters\EncrypterInterface
 * @method \mako\security\crypto\encrypters\EncrypterInterface instance(?string $configuration = null)
 * @method \mako\security\crypto\encrypters\EncrypterInterface getInstance(?string $configuration = null)
 */
class CryptoManager extends AdapterManager
{
	/**
	 * OpenSSL encrypter factory.
	 */
	protected function opensslFactory(array $configuration): OpenSSL
	{
		return new OpenSSL(Key::decode($configuration['key']), $configuration['cipher']);
	}

	/**
	 * Returns a crypto instance.
	 */
	#[Override]
	protected function instantiate(string $configuration): Crypto
	{
		if (!isset($this->configurations[$configuration])) {
			throw new CryptoException(sprintf('[ %s ] has not been defined in the crypto configuration.', $configuration));
		}

		$configuration = $this->configurations[$configuration];

		return new Crypto($this->factory($configuration['library'], $configuration), $this->container->get(Signer::class));
	}

	/**
	 * Returns an instance of the chosen encrypter. Alias of CryptoManager::getInstance().
	 */
	public function getEncrypter(?string $configuration = null): EncrypterInterface
	{
		return $this->getInstance($configuration);
	}
}
