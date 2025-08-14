<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\bus\command\CommandBus;
use mako\bus\command\CommandBusInterface;
use mako\bus\event\EventBus;
use mako\bus\event\EventBusInterface;
use mako\bus\query\QueryBus;
use mako\bus\query\QueryBusInterface;
use Override;

/**
 * Bus service.
 */
abstract class BusService extends Service
{
	/**
	 * Should the command bus be registered?
	 */
	protected bool $registerCommandBus = true;

	/**
	 * Should the event bus be registered?
	 */
	protected bool $registerEventBus = true;

	/**
	 * Should the query bus be registered?
	 */
	protected bool $registerQueryBus = true;

	/**
	 * Returns an array of command handlers.
	 *
	 * @return array<string, callable|string>
	 */
	protected function getCommandHandlers(): array
	{
		return [];
	}

	/**
	 * Returns an array of event handlers.
	 *
	 * @return array<string, callable|string>
	 */
	protected function getEventHandlers(): array
	{
		return [];
	}

	/**
	 * Returns an array of query handlers.
	 *
	 * @return array<string, callable|string>
	 */
	protected function getQueryHandlers(): array
	{
		return [];
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function register(): void
	{
		// Register command bus

		if ($this->registerCommandBus) {
			$commandHandlers = $this->getCommandHandlers();

			$this->container->registerSingleton([CommandBusInterface::class, 'commandBus'], static function ($container) use ($commandHandlers) {
				$commandBus = new CommandBus($container);

				foreach ($commandHandlers as $command => $handler) {
					$commandBus->registerHandler($command, $handler);
				}

				return $commandBus;
			});
		}

		// Register event bus

		if ($this->registerEventBus) {
			$eventHandlers = $this->getEventHandlers();

			$this->container->registerSingleton([EventBusInterface::class, 'eventBus'], static function ($container) use ($eventHandlers) {
				$eventBus = new EventBus($container);

				foreach ($eventHandlers as $event => $handlers) {
					foreach ((array) $handlers as $handler) {
						$eventBus->registerHandler($event, $handler);
					}
				}

				return $eventBus;
			});
		}

		// Register query bus

		if ($this->registerQueryBus) {
			$queryHandlers = $this->getQueryHandlers();

			$this->container->registerSingleton([QueryBusInterface::class, 'queryBus'], static function ($container) use ($queryHandlers) {
				$queryBus = new QueryBus($container);

				foreach ($queryHandlers as $query => $handler) {
					$queryBus->registerHandler($query, $handler);
				}

				return $queryBus;
			});
		}
	}
}
