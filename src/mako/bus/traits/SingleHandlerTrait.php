<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\bus\traits;

use mako\bus\exceptions\BusException;
use Override;

/**
 * Single handler trait.
 */
trait SingleHandlerTrait
{
	use ResolveHandlerTrait;

	/**
	 *  Handlers.
	 */
	protected array $handlers = [];

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function registerHandler(string $className, callable|string $handler): void
	{
		$this->handlers[$className] = $handler;
	}

	/**
	 * Returns the exception thrown when unable to resolve a handler.
	 */
	abstract protected function getUnableToResolveException(object $object): BusException;

	/**
	 * Gets the handler.
	 */
	protected function getHandler(object $object): callable
	{
		if (!isset($this->handlers[$object::class])) {
			throw $this->getUnableToResolveException($object);
		}

		$handler = $this->handlers[$object::class];

		return $this->resolveHandler($handler);
	}
}
