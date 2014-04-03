<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\event;

use \Closure;
use \RuntimeExcepion;

/**
 * Observable trait.
 *
 * @author  Frederic G. Østby
 */

trait ObservableTrait
{
	//---------------------------------------------
	// Trait properties
	//---------------------------------------------

	/**
	 * Observers.
	 * 
	 * @var array
	 */

	protected $_observers = [];

	//---------------------------------------------
	// Trait methods
	//---------------------------------------------

	/**
	 * Attach an observer.
	 * 
	 * @access  public
	 * @param   string                  $event     Event name
	 * @param   string|object|\Closure  $observer  Observer class name, observer instance or observer closure
	 */

	public function attachObserver($event, $observer)
	{
		$this->_observers[$event][] = $observer;
	}
	
	/**
	 * Detach an observer.
	 * 
	 * @access  public
	 * @param   string         $event     Event name
	 * @param   string|object  $observer  Observer class name or observer instance
	 */

	public function detachObserver($event, $observer)
	{
		if(isset($this->_observers[$event]))
		{
			foreach($this->_observers[$event] as $key => $_observer)
			{
				if($_observer instanceof $observer || $_observer === $observer)
				{
					unset($this->_observers[$event][$key]);
				}
			}
		}
	}

	/**
	 * Clear all observers.
	 * 
	 * @access  public
	 * @param   string  $event  (optional) Event name
	 */

	public function clearObservers($event = null)
	{
		if($event === null)
		{
			$this->_observers = [];
		}
		else
		{
			$this->_observers[$event] = [];
		}
	}

	/**
	 * Overrides an observer.
	 * 
	 * @access  public
	 * @param   string                  $event    Event name
	 * @param   string|object|\Closure  $closure  Event handler
	 */

	public function overrideObservers($event, $observer)
	{
		$this->clearObservers($event);

		$this->attachObserver($event, $observer);
	}

	/**
	 * Notify all observers.
	 * 
	 * @access  public
	 * @param   string   $event       Event name
	 * @param   array    $parameters  (optional) Parameters
	 * @param   boolean  $break       (optional) Break if one of the observers returns false?
	 */

	protected function notifyObservers($event, array $parameters = [], $break = false)
	{
		$returnValues = [];

		// Notify observers

		if(isset($this->_observers[$event]))
		{
			foreach($this->_observers[$event] as $observer)
			{
				if(!is_object($observer))
				{
					$observer = [new $observer, 'update'];
				}
				elseif(!($observer instanceof Closure))
				{
					$observer = [$observer, 'update'];
				}

				$returnValues[] = $last = call_user_func_array($observer, $parameters);

				if($break && $last === false)
				{
					break;
				}
			}
		}

		// Return all return values from the observers

		return $returnValues;
	}
}