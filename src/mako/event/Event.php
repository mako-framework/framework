<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\event;

use Closure;

use mako\event\EventHandlerInterface;
use mako\syringe\Container;

/**
 * Event listener.
 *
 * @author  Frederic G. Østby
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
	 * @access  public
	 * @param   null|\mako\syringe\Container  $container  IoC container
	 */

	public function __construct(Container $container = null)
	{
		$this->container = $container ?: new Container;
	}

	/**
	 * Registers an event handler.
	 *
	 * @access  public
	 * @param   string           $name     Event name
	 * @param   string|\Closure  $handler  Event handler
	 */

	public function register($name, $handler)
	{
		$this->events[$name][] = $handler;
	}

	/**
	 * Returns TRUE if an event listener is registered for the event and FALSE if not.
	 *
	 * @access  public
	 * @param   string   $name  Event name
	 * @return  boolean
	 */

	public function has($name)
	{
		return ! empty($this->events[$name]);
	}

	/**
	 * Returns an array of all registered events.
	 *
	 * @access  public
	 * @return  arary
	 */

	public function events()
	{
		return array_keys($this->events);
	}

	/**
	 * Clears all events handlers for the specified event.
	 *
	 * @access  public
	 * @param   string  $name  Event name
	 */

	public function clear($name)
	{
		unset($this->events[$name]);
	}

	/**
	 * Overrides an event.
	 *
	 * @access  public
	 * @param   string    $name            Event name
	 * @param   string|\Closure  $handler  Event handler
	 */

	public function override($name, $handler)
	{
		$this->clear($name);

		$this->register($name, $handler);
	}

	/**
	 * Executes a closure handler and returns the response.
	 *
	 * @access  protected
	 * @param   \Closure   $handler     Event handler
	 * @param   array      $parameters  Parameters
	 * @return  mixed
	 */

	protected function executeClosureHandler(Closure $handler, array $parameters)
	{
		return $this->container->call($handler, $parameters);
	}

	/**
	 * Resolves a class handler.
	 *
	 * @access  protected
	 * @param   string                             $handler  Event handler class
	 * @param   \mako\event\EventHandlerInterface
	 */

	protected function resolveHandler($handler)
	{
		return $this->container->get($handler);
	}

	/**
	 * Executes a class handler and returns the response.
	 *
	 * @access  protected
	 * @param   \mako\event\EventHandlerInterface  $handler     Event handler
	 * @param   array                              $parameters  Parameters
	 * @return  mixed
	 */

	protected function executeClassHandler(EventHandlerInterface $handler, array $parameters)
	{
		return $this->container->call([$handler, 'handle'], $parameters);
	}

	/**
	 * Executes the event handler and returns the response.
	 *
	 * @access  protected
	 * @param   string|\Closure  $handler     Event handler
	 * @param   array            $parameters  Parameters
	 * @return  mixed
	 */

	protected function executeHandler($handler, array $parameters)
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
	 * @access  public
	 * @param   string   $name        Event name
	 * @param   array    $parameters  Parameters
	 * @param   boolean  $break       Break if one of the closures returns false?
	 * @return  array
	 */

	public function trigger($name, array $parameters = [], $break = false)
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