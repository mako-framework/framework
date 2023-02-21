<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\bus;

/**
 * Handler interface.
 */
interface HandlerInterface
{
	/**
	 * Registers an object handler.
	 */
	public function registerHandler(string $class, callable|string $handler): void;

	/**
	 * Handles an object instance.
	 */
	public function handle(object $object);
}
