<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\bus\command;

use mako\bus\HandlerInterface;

/**
 * Command bus interface.
 */
interface CommandBusInterface extends HandlerInterface
{
	/**
	 * Registers a command handler.
	 */
	public function registerHandler(string $commandClass, callable|string $handler): void;

	/**
	 * Handles a command.
	 */
	public function handle(object $command): void;
}
