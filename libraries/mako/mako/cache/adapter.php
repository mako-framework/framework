<?php

namespace mako\cache
{
	use \Closure;
	use \InvalidArgumentException;

	/**
	* Cache adapter core.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	abstract class Adapter
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------

		/**
		* Cache identifier.
		*/

		protected $identifier;

		//---------------------------------------------
		// Class constructor, destructor etc ...
		//---------------------------------------------

		/**
		* Constructor.
		*
		* @access  public
		* @param   string  Cache identifier
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

		abstract public function delete($key);

		abstract public function clear();

		/**
		* Store multiple variables in the cache.
		*
		* @access  public
		* @param   array    An array of cache keys
		* @param   int      (optional) Time to live
		* @return  boolean
		*/

		final public function writeMulti(array $items, $ttl = 0)
		{
			$success = true;
			
			foreach($items as $key => $value)
			{
				if($this->write($key, $value, $ttl) === false)
				{
					$success = false;
				}
			}

			return $success;
		}

		/**
		* Fetch multiple variables from the cache.
		*
		* @access  public
		* @param   array   An array of cache keys
		* @return  array
		*/

		final public function readMulti(array $keys)
		{
			$cache = array();

			foreach($keys as $key)
			{
				$cache[$key] = $this->read($key);
			}

			return $cache;
		}
		
		/**
		* Delete multiple variables from the cache.
		*
		* @access  public
		* @param   array    An array of cache keys
		* @return  boolean
		*/
		
		final public function deleteMulti(array $keys)
		{
			$success = true;
			
			foreach($keys as $key)
			{
				if($this->delete($key) === false)
				{
					$success = false;
				}
			}

			return $success;
		}

		/**
		* Fetches variable from cache and stores it if it doesn't exist.
		*
		* @access  public
		* @param   string   Cache key
		* @param   closure  Closure (anonymous function) that returns value to store if it doesn't already exist
		* @param   int      (optional) Time to live
		* @return  mixed
		*/
		
		final public function remember($key, $closure, $ttl = 0)
		{
			$item = $this->read($key);
			
			if($item === false)
			{
				if(!($closure instanceof Closure))
				{
					throw new InvalidArgumentException(__CLASS__.': ' . __METHOD__ . ' expects a closure.');
				}

				$item = call_user_func($closure);
				
				$this->write($key, $item, $ttl);
			}
			
			return $item;
		}
		
		/**
		* Magic setter.
		*
		* @access  public
		* @param   string  Cache key
		* @param   mixed   The variable to store
		*/

		final public function __set($key, $value)
		{
			$this->write($key, $value);
		}

		/**
		* Magic getter.
		*
		* @access  public
		* @param   string  Cache key
		* @return  mixed
		*/

		final public function __get($key)
		{
			return $this->read($key);
		}

		/**
		* Magic unsetter.
		*
		* @access  public
		* @param   string  Cache key
		*/

		final public function __unset($key)
		{
			$this->delete($key);
		}
	}
}

/** -------------------- End of file --------------------**/