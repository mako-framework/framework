<?php

namespace mako\event;

use \Closure;
use \RuntimeExcepion;

/**
 * Observable trait.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
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

	protected $_observers = array();

	/**
	 * Static (global) observers.
	 * 
	 * @var array
	 */

	protected static $_staticObservers = array();

	//---------------------------------------------
	// Trait methods
	//---------------------------------------------

	/**
	 * Attach an observer.
	 * 
	 * @access  public
	 * @param   string                  $event     Event name
	 * @param   string|object|\Closure  $observer  Observer instance
	 */

	public function attachObserver($event, $observer)
	{
		$this->_observers[$event][] = $observer;
	}

	/**
	 * Attach a static observer.
	 * 
	 * @access  public
	 * @param   string                  $event     Event name
	 * @param   string|object|\Closure  $observer  Observer instance
	 */

	public static function attachStaticObserver($event, $observer)
	{
		static::$_staticObservers[$event][] = $observer;
	}

	/**
	 * Detach an observer.
	 * 
	 * @access  public
	 * @param   string         $event     Event name
	 * @param   string|object  $observer  Observer instance or observer class name
	 */

	public function detachObserver($event, $observer)
	{
		if(!isset($this->_observers[$event]))
		{
			throw new RuntimeException(vsprintf("%s(): The [ %s ] event does not exist.", array(__METHOD__, $event)));
		}

		foreach($this->_observers[$event] as $key => $_observer)
		{
			if($_observer instanceof $observer || $_observer === $observer)
			{
				unset($this->_observers[$event][$key]);
			}
		}
	}

	/**
	 * Detach a static observer.
	 * 
	 * @access  public
	 * @param   string         $event     Event name
	 * @param   object|string  $observer  Observer instance or observer class name
	 */

	public static function detachStaticObserver($event, $observer)
	{
		if(!isset(static::$_staticObservers[$event]))
		{
			throw new RuntimeException(vsprintf("%s(): The [ %s ] event does not exist.", array(__METHOD__, $event)));
		}

		foreach(static::$_staticObservers[$event] as $key => $_observer)
		{
			if($_observer instanceof $observer || $_observer === $observer)
			{
				unset(static::$_staticObservers[$event][$key]);
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
			$this->_observers = array();
		}
		else
		{
			$this->_observers[$event] = array();
		}
	}

	/**
	 * Clear all static observers.
	 * 
	 * @access  public
	 * @param   string  $event  (optional) Event name
	 */

	public static function clearStaticObservers($event = null)
	{
		if($event === null)
		{
			static::$_staticObservers = array();
		}
		else
		{
			static::$_staticObservers[$event] = array();
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
	 * Overrides a static observer.
	 * 
	 * @access  public
	 * @param   string                  $event    Event name
	 * @param   string|object|\Closure  $closure  Event handler
	 */

	public static function overrideStaticObservers($event, $observer)
	{
		static::clearStaticObservers($event);

		static::attachStaticObserver($event, $observer);
	}

	/**
	 * Notify all observers.
	 * 
	 * @access  public
	 * @param   string   $event       Event name
	 * @param   array    $parameters  (optional) Parameters
	 * @param   boolean  $break       (optional) Break if one of the observers returns false?
	 */

	protected function notifyObservers($event, array $parameters = array(), $break = false)
	{
		$returnValues = array();

		// Merge observers and static observers

		$observers = array_merge
		(
			isset($this->_observers[$event]) ? $this->_observers[$event] : array(),
			isset(static::$_staticObservers[$event]) ? static::$_staticObservers[$event] : array()
		);

		// Notify observers

		foreach($observers as $observer)
		{
			if(!is_object($observer))
			{
				$observer = array(new $observer, 'update');
			}
			elseif(!($observer instanceof Closure))
			{
				$observer = array($observer, 'update');
			}

			$returnValues[] = $last = call_user_func_array($observer, $parameters);

			if($break && $last === false)
			{
				break;
			}
		}

		// Return all return values from the observers

		return $returnValues;
	}
}

/** -------------------- End of file -------------------- **/