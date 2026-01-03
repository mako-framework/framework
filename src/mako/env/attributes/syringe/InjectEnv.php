<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\env\attributes\syringe;

use Attribute;
use mako\env\Type;
use mako\syringe\attributes\InjectorInterface;
use mako\syringe\Container;
use Override;
use ReflectionParameter;

use function mako\env;

/**
 * Environment variable variable injector.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class InjectEnv implements InjectorInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $variableName,
		protected mixed $default = null,
		protected bool $localOnly = false,
		protected ?Type $as = null
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
    public function getParameterValue(Container $container, ReflectionParameter $parameter): mixed
    {
		return env($this->variableName, $this->default, $this->localOnly, $this->as);
	}
}
