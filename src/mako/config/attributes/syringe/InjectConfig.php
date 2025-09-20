<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\config\attributes\syringe;

use Attribute;
use mako\config\Config;
use mako\syringe\attributes\InjectorInterface;
use mako\syringe\Container;
use Override;
use ReflectionParameter;

/**
 * Config value injector.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class InjectConfig implements InjectorInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $key,
		protected mixed $default = null
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
    public function getParameterValue(Container $container, ReflectionParameter $parameter): mixed
    {
		return $container->get(Config::class)->get($this->key, $this->default);
	}
}
