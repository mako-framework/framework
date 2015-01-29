<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\security\crypto;

use RuntimeException;

use mako\common\AdapterManager;
use mako\security\crypto\Crypto;
use mako\security\crypto\encrypters\MCrypt;
use mako\security\crypto\encrypters\OpenSSL;
use mako\security\crypto\padders\PKCS7;

/**
 * Crypto manager.
 *
 * @author  Frederic G. Østby
 *
 * @method  \mako\security\crypto\encrypters\EncrypterInterface  instance($configuration = null)
 */

class CryptoManager extends AdapterManager
{
	/**
	 * Reuse instances?
	 *
	 * @var boolean
	 */

	protected $reuseInstances = false;

	/**
	 * MCrypt encrypter factory.
	 *
	 * @access  protected
	 * @param   array                                    $configuration  Configuration
	 * @return  \mako\security\crypto\encrypters\MCrypt
	 */

	protected function mcryptFactory($configuration)
	{
		return new MCrypt($configuration['key'], new PKCS7(), $configuration['cipher'], $configuration['mode']);
	}

	/**
	 * OpenSSL encrypter factory.
	 *
	 * @access  protected
	 * @param   array                                     $configuration  Configuration
	 * @return  \mako\security\crypto\encrypters\OpenSSL
	 */

	protected function opensslFactory($configuration)
	{
		return new OpenSSL($configuration['key'], $configuration['cipher']);
	}

	/**
	 * Returns a cache instance.
	 *
	 * @access  public
	 * @param   string                        $configuration  Configuration name
	 * @return  \mako\security\crypto\Crypto
	 */

	protected function instantiate($configuration)
	{
		if(!isset($this->configurations[$configuration]))
		{
			throw new RuntimeException(vsprintf("%s(): [ %s ] has not been defined in the crypto configuration.", [__METHOD__, $connection]));
		}

		$configuration = $this->configurations[$configuration];

		$factoryMethod = $this->getFactoryMethodName($configuration['library']);

		$instance = new Crypto($this->$factoryMethod($configuration));

		if($this->container->has('signer'))
		{
			$instance->setSigner($this->container->get('signer'));
		}

		return $instance;
	}
}