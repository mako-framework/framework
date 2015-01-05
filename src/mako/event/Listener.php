<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\event;

use Closure;

/**
 * Event listener.
 *
 * @author  Frederic G. Østby
 */

class Listener
{
	/**
	 * Array of events.
	 *
	 * @var array
	 */

	protected $events = [];

	/**
	 * Adds an event listener to the queue.
	 *
	 * @access  public
	 * @param   string    $name     Event name
	 * @param   \Closure  $closure  Event handler
	 */

	public function register($name, Closure $closure)
	{
		$this->events[$name][] = $closure;
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
	 * Clears all events or just the events for a specific trigger.
	 *
	 * @access  public
	 * @param   string  $name  Event name
	 */

	public function clear($name = null)
	{
		if($name === null)
		{
			$this->events = [];
		}
		else
		{
			$this->events[$name] = [];
		}
	}

	/**
	 * Overrides an event.
	 * 
	 * @access  public
	 * @param   string    $name     Event name
	 * @param   \Closure  $closure  Event handler
	 */

	public function override($name, Closure $closure)
	{
		$this->clear($name);

		$this->register($name, $closure);
	}

	/**
	 * Runs all closures for an event and returns an array 
	 * contaning the return values of each event handler.
	 *
	 * @access  public
	 * @param   string   $name    Event name
	 * @param   array    $params  Closure parameters
	 * @param   boolean  $break   Break if one of the closures returns false?
	 * @return  array
	 */

	public function trigger($name, array $params = [], $break = false)
	{
		$returnValues = [];

		if(isset($this->events[$name]))
		{
			foreach($this->events[$name] as $event)
			{
				$returnValues[] = $last = call_user_func_array($event, $params);

				if($break && $last === false)
				{
					break;
				}
			}
		}

		return $returnValues;
	}
}