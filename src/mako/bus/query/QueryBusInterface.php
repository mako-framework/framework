<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\bus\query;

use mako\bus\HandlerInterface;

/**
 * Query bus interface.
 */
interface QueryBusInterface extends HandlerInterface
{
	/**
	 * Registers a query handler.
	 */
	public function registerHandler(string $queryClass, callable|string $handler): void;

	/**
	 * Handles a query.
	 */
	public function handle(object $query): mixed;
}
