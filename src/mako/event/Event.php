<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\event;

use Closure;
use mako\syringe\Container;

use function array_keys;

/**
 * Event listener.
 */
class Event
{
	/**
	 * Container.
	 *
	 * @var \mako\syringe\Container
	 */
	protected $container;

	/**
	 * Registered event listeners.
	 *
	 * @var array
	 */
	protected $events = [];

	/**
	 * Constructor.
	 *
	 * @param \mako\syringe\Container|null $container Container
	 */
	public function __construct(?Container $container = null)
	{
		$this->container = $container ?? new Container;
	}

	/**
	 * Registers an event handler.
	 *
	 * @param string          $name    Event name
	 * @param \Closure|string $handler Event handler
	 */
	public function register(string $name, $handler): void
	{
		$this->events[$name][] = $handler;
	}

	/**
	 * Returns TRUE if an event listener is registered for the event and FALSE if not.
	 *
	 * @param  string $name Event name
	 * @return bool
	 */
	public function has(string $name): bool
	{
		return !empty($this->events[$name]);
	}

	/**
	 * Returns an array of all registered events.
	 *
	 * @return array
	 */
	public function events(): array
	{
		return array_keys($this->events);
	}

	/**
	 * Clears all events handlers for the specified event.
	 *
	 * @param string $name Event name
	 */
	public function clear(string $name): void
	{
		unset($this->events[$name]);
	}

	/**
	 * Overrides an event.
	 *
	 * @param string          $name    Event name
	 * @param \Closure|string $handler Event handler
	 */
	public function override(string $name, $handler): void
	{
		$this->clear($name);

		$this->register($name, $handler);
	}

	/**
	 * Executes a closure handler and returns the response.
	 *
	 * @param  \Closure $handler    Event handler
	 * @param  array    $parameters Parameters
	 * @return mixed
	 */
	protected function executeClosureHandler(Closure $handler, array $parameters): mixed
	{
		return $this->container->call($handler, $parameters);
	}

	/**
	 * Resolves a class handler.
	 *
	 * @param  string                            $handler Event handler class
	 * @return \mako\event\EventHandlerInterface
	 */
	protected function resolveHandler(string $handler): EventHandlerInterface
	{
		return $this->container->get($handler);
	}

	/**
	 * Executes a class handler and returns the response.
	 *
	 * @param  \mako\event\EventHandlerInterface $handler    Event handler
	 * @param  array                             $parameters Parameters
	 * @return mixed
	 */
	protected function executeClassHandler(EventHandlerInterface $handler, array $parameters): mixed
	{
		return $this->container->call([$handler, 'handle'], $parameters);
	}

	/**
	 * Executes the event handler and returns the response.
	 *
	 * @param  \Closure|string $handler    Event handler
	 * @param  array           $parameters Parameters
	 * @return mixed
	 */
	protected function executeHandler($handler, array $parameters): mixed
	{
		if($handler instanceof Closure)
		{
			return $this->executeClosureHandler($handler, $parameters);
		}

		$handler = $this->resolveHandler($handler);

		return $this->executeClassHandler($handler, $parameters);
	}

	/**
	 * Runs all closures for an event and returns an array
	 * contaning the return values of each event handler.
	 *
	 * @param  string $name       Event name
	 * @param  array  $parameters Parameters
	 * @param  bool   $break      Break if one of the closures returns false?
	 * @return array
	 */
	public function trigger(string $name, array $parameters = [], bool $break = false): array
	{
		$returnValues = [];

		if(isset($this->events[$name]))
		{
			foreach($this->events[$name] as $handler)
			{
				$returnValues[] = $last = $this->executeHandler($handler, $parameters);

				if($break && $last === false)
				{
					break;
				}
			}
		}

		return $returnValues;
	}
}
