<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\attributes\syringe;

use Attribute;
use mako\cache\CacheManager;
use mako\cache\psr16\SimpleCache;
use mako\syringe\attributes\InjectorInterface;
use mako\syringe\Container;
use Override;
use Psr\SimpleCache\CacheInterface;
use ReflectionParameter;

/**
 * SimpleCache injector.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class InjectSimpleCache implements InjectorInterface
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
    public function getParameterValue(Container $container, ReflectionParameter $parameter): CacheInterface
    {
		return new SimpleCache($container->get(CacheManager::class)->getInstance($this->configuration));
	}
}
