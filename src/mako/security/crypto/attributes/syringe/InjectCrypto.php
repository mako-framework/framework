<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security\crypto\attributes\syringe;

use Attribute;
use mako\security\crypto\Crypto;
use mako\security\crypto\CryptoManager;
use mako\syringe\attributes\InjectorInterface;
use mako\syringe\Container;
use Override;
use ReflectionParameter;

/**
 * Crypto injector.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class InjectCrypto implements InjectorInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected ?string $configuration = null
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
    public function getParameterValue(Container $container, ReflectionParameter $parameter): Crypto
    {
		return $container->get(CryptoManager::class)->getInstance($this->configuration);
	}
}
