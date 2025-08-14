<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\bus\command;

use mako\bus\HandlerInterface;
use Override;

/**
 * Command bus interface.
 */
interface CommandBusInterface extends HandlerInterface
{
	/**
	 * Registers a command handler.
	 */
	#[Override]
	public function registerHandler(string $commandClass, callable|string $handler): void;

	/**
	 * Handles a command.
	 */
	#[Override]
	public function handle(object $command): void;
}
