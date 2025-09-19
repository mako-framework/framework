<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\redis\attributes\syringe;

use Attribute;
use mako\redis\ConnectionManager;
use mako\redis\Redis;
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
		protected ?string $connection = null,
		protected ?ConnectionManager $connectionManager = null
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
    public function getParameterValue(ReflectionParameter $parameter): Redis
    {
		return $this->connectionManager->getConnection($this->connection);
	}
}
