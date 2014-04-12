<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\security\crypto;

use \RuntimeException;

use \mako\security\crypto\Crypto;
use \mako\security\crypto\adapters\MCrypt;
use \mako\security\crypto\adapters\OpenSSL;

/**
 * Crypto manager.
 *
 * @author  Frederic G. Østby
 */

class CryptoManager extends \mako\common\AdapterManager
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Reuse instances?
	 * 
	 * @var boolean
	 */

	const REUSE_INSTANCES = false;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * MCrypt adapter factory.
	 * 
	 * @access  protected
	 * @param   array                                  $configuration  Configuration
	 * @return  \mako\security\crypto\adapters\MCrypt
	 */

	protected function mcryptAdapter($configuration)
	{
		return new MCrypt($configuration['key'], $configuration['cipher'], $configuration['mode']);
	}

	/**
	 * OpenSSL adapter factory.
	 * 
	 * @access  protected
	 * @param   array                                   $configuration  Configuration
	 * @return  \mako\security\crypto\adapters\OpenSSL
	 */

	protected function opensslAdapter($configuration)
	{
		return new OpenSSL($configuration['key'], $configuration['cipher']);
	}

	/**
	 * Returns a cache instance.
	 * 
	 * @access  public
	 * @param   string             $configuration  Configuration name
	 * @return  \mako\cache\Cache
	 */

	protected function instantiate($configuration)
	{
		if(!isset($this->configurations[$configuration]))
		{
			throw new RuntimeException(vsprintf("%s(): [ %s ] has not been defined in the crypto configuration.", [__METHOD__, $connection]));
		}

		$configuration = $this->configurations[$configuration];

		$factoryMethod = $this->getFactoryMethodName($configuration['library']);

		return new Crypto($this->$factoryMethod($configuration), $this->container->get('signer'));
	}
}