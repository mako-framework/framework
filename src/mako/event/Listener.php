<?php

namespace mako\event;

use \Closure;

/**
 * Event listener.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Listener
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Array of events.
	 *
	 * @var array
	 */

	protected $events = [];

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 *
	 * @access  protected
	 */

	public function __construct()
	{
		// Nothing here
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

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

	public function registered($name)
	{
		return isset($this->events[$name]);
	}

	/**
	 * Clears all events or just the events for a specific trigger.
	 *
	 * @access  public
	 * @param   string  $name  (optional) Event name
	 */

	public function clear($name = null)
	{
		if($name === null)
		{
			$this->events = [];
		}
		else
		{
			unset($this->events[$name]);
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
	 * @param   array    $params  (optional) Closure parameters
	 * @param   boolean  $break   (optional) Break if one of the closures returns false?
	 * @return  array
	 */

	public function trigger($name, array $params = [], $break = false)
	{
		$values = [];

		if(isset($this->events[$name]))
		{
			foreach($this->events[$name] as $event)
			{
				$values[] = $last = call_user_func_array($event, $params);

				if($break && $last === false)
				{
					return $values;
				}
			}
		}

		return $values;
	}
}

/** -------------------- End of file -------------------- **/