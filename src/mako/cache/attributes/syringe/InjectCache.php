<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\attributes\syringe;

use Attribute;
use mako\cache\CacheManager;
use mako\cache\stores\StoreInterface;
use mako\syringe\attributes\InjectorInterface;
use mako\syringe\Container;
use Override;
use ReflectionParameter;

/**
 * Cache injector.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class InjectCache implements InjectorInterface
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
    public function getParameterValue(Container $container, ReflectionParameter $parameter): StoreInterface
    {
		return $container->get(CacheManager::class)->getInstance($this->configuration);
	}
}
