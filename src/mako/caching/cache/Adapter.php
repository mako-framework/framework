<?php

namespace mako\cache;

use \Closure;

/**
 * Cache adapter.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

abstract class Adapter
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Cache identifier.
	 *
	 * @var string
	 */

	protected $identifier;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   string  $identifier Cache identifier
	 */
	
	public function __construct($identifier)
	{
		$this->identifier = md5($identifier);
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	abstract public function write($key, $value, $ttl = 0);

	abstract public function read($key);

	abstract public function has($key);

	abstract public function increment($key, $ammount = 1);

	abstract public function decrement($key, $ammount = 1);

	abstract public function delete($key);

	abstract public function clear();

	/**
	 * Fetches variable from cache and stores it if it doesn't exist.
	 *
	 * @access  public
	 * @param   string   $key      Cache key
	 * @param   Closure  $closure  Closure that returns value to store if it doesn't already exist
	 * @param   int      $ttl      (optional) Time to live
	 * @return  mixed
	 */
	
	final public function remember($key, Closure $closure, $ttl = 0)
	{
		$item = $this->read($key);
		
		if($item === false)
		{
			$item = $closure();
			
			$this->write($key, $item, $ttl);
		}
		
		return $item;
	}
	
	/**
	 * Magic setter.
	 *
	 * @access  public
	 * @param   string  $key    Cache key
	 * @param   mixed   $value  The variable to store
	 */

	final public function __set($key, $value)
	{
		$this->write($key, $value);
	}

	/**
	 * Magic getter.
	 *
	 * @access  public
	 * @param   string  $key  Cache key
	 * @return  mixed
	 */

	final public function __get($key)
	{
		return $this->read($key);
	}

	/**
	 * Magic isset.
	 *
	 * @access  public
	 * @param   string   $key  Cache key
	 * @return  boolean
	 */

	final public function __isset($key)
	{
		return ($this->read($key) !== false);
	}

	/**
	 * Magic unsetter.
	 *
	 * @access  public
	 * @param   string  $key  Cache key
	 */

	final public function __unset($key)
	{
		$this->delete($key);
	}
}

/** -------------------- End of file -------------------- **/