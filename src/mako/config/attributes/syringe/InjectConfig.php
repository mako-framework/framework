<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\config\attributes\syringe;

use Attribute;
use mako\config\Config;
use mako\syringe\attributes\InjectorInterface;

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
		protected mixed $default,
		protected Config $config
	) {
	}

    /**
     * {@inheritDoc}
     */
    public function getParameterValue(): mixed
    {
		return $this->config->get($this->key, $this->default);
	}
}
