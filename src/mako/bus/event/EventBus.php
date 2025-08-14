<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\bus\event;

use Generator;
use mako\bus\traits\ResolveHandlerTrait;
use mako\syringe\Container;
use Override;

/**
 * Event bus.
 */
class EventBus implements EventBusInterface
{
	use ResolveHandlerTrait;

	/**
	 * Event handlers.
	 */
	protected array $handlers = [];

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
	#[Override]
	public function registerHandler(string $eventClass, callable|string $handler): void
	{
		$this->handlers[$eventClass][] = $handler;
	}

	/**
	 * Yields the handlers for an event.
	 */
	protected function getHandlers(object $event): Generator
	{
		if (isset($this->handlers[$event::class])) {
			foreach ($this->handlers[$event::class] as $handler) {
				yield $this->resolveHandler($handler);
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function handle(object $event): void
	{
		/** @var callable $handler */
		foreach ($this->getHandlers($event) as $handler) {
			$handler($event);
		}
	}
}
