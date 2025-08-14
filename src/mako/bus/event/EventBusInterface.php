<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\bus\event;

use mako\bus\HandlerInterface;
use Override;

/**
 * Event bus interface.
 */
interface EventBusInterface extends HandlerInterface
{
	/**
	 * Registers an event handler.
	 */
	#[Override]
	public function registerHandler(string $eventClass, callable|string $handler): void;

	/**
	 * Handles an event.
	 */
	#[Override]
	public function handle(object $event): void;
}
