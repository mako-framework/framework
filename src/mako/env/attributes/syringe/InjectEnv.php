<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\env\attributes\syringe;

use Attribute;
use mako\syringe\attributes\InjectorInterface;
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
		protected bool $isBool = false,
		protected bool $localOnly = false
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
    public function getParameterValue(ReflectionParameter $parameter): mixed
    {
		return env($this->variableName, $this->default, $this->isBool, $this->localOnly);
	}
}
