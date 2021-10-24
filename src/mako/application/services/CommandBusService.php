<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\commander\CommandBus;
use mako\commander\CommandBusInterface;

/**
 * Command bus service.
 */
class CommandBusService extends Service
{
	/**
	 * {@inheritDoc}
	 */
	public function register(): void
	{
		$this->container->registerSingleton([CommandBusInterface::class, 'commander'], static fn($container) => new CommandBus($container));
	}
}
