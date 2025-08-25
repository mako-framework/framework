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
		protected ?string $connection,
		protected ConnectionManager $connectionManager
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
    public function getParameterValue(ReflectionParameter $parameter): Connection
    {
		return $this->connectionManager->getConnection($this->connection);
	}
}
