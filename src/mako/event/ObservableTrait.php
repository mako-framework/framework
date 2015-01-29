<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\event;

use Closure;

/**
 * Observable trait.
 *
 * @author  Frederic G. Østby
 */

trait ObservableTrait
{
	/**
	 * Observers.
	 *
	 * @var array
	 */

	protected $_observers = [];

	/**
	 * Attach an observer.
	 *
	 * @access  public
	 * @param   string    $event     Event name
	 * @param   \Closure  $observer  Observer closure
	 */

	public function attachObserver($event, Closure $observer)
	{
		$this->_observers[$event][] = $observer;
	}

	/**
	 * Returns TRUE if the event has any observers and FALSE if not.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function hasObserver($event)
	{
		return ! empty($this->_observers[$event]);
	}

	/**
	 * Clear all observers.
	 *
	 * @access  public
	 * @param   string  $event  Event name
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
	 * @param   string    $event     Event name
	 * @param   \Closure  $observer  Event handler
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
	 * @param   array    $parameters  Parameters
	 * @param   boolean  $break       Break if one of the observers returns false?
	 */

	protected function notifyObservers($event, array $parameters = [], $break = false)
	{
		$returnValues = [];

		if(isset($this->_observers[$event]))
		{
			foreach($this->_observers[$event] as $observer)
			{
				$returnValues[] = $last = call_user_func_array($observer, $parameters);

				if($break && $last === false)
				{
					break;
				}
			}
		}

		return $returnValues;
	}
}