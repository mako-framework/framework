<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\bus\command;

use mako\bus\command\exceptions\CommandBusException;
use mako\bus\traits\SingleHandlerTrait;
use mako\syringe\Container;

use function sprintf;

/**
 * Command bus.
 */
class CommandBus implements CommandBusInterface
{
	use SingleHandlerTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Container $container
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getUnableToResolveException(object $object): CommandBusException
	{
		return new CommandBusException(sprintf('No handler has been registered for [ %s ] commands.', $object::class));
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(object $command): void
	{
		$this->getHandler($command)($command);
	}
}
