<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\attributes\syringe;

use Attribute;
use mako\database\ConnectionManager;
use mako\database\connections\Connection;
use mako\syringe\attributes\InjectorInterface;
use mako\syringe\Container;
use Override;
use ReflectionParameter;

/**
 * Connection injector.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class InjectConnection implements InjectorInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected ?string $connection = null
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
    public function getParameterValue(Container $container, ReflectionParameter $parameter): Connection
    {
		return $container->get(ConnectionManager::class)->getConnection($this->connection);
	}
}
