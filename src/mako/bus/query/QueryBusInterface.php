<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\bus\query;

use mako\bus\HandlerInterface;
use Override;

/**
 * Query bus interface.
 */
interface QueryBusInterface extends HandlerInterface
{
	/**
	 * Registers a query handler.
	 */
	#[Override]
	public function registerHandler(string $queryClass, callable|string $handler): void;

	/**
	 * Handles a query.
	 */
	#[Override]
	public function handle(object $query): mixed;
}
