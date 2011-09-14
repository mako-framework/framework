<?php

namespace mako\cache
{	
	/**
	* Non-persistent memory based cache.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class Memory extends \mako\cache\Adapter
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------

		protected $cache = array();

		//---------------------------------------------
		// Class constructor, destructor etc ...
		//---------------------------------------------

		/**
		* Constructor.
		*
		* @access  public
		* @param   array   Configuration
		*/

		public function __construct(array $config)
		{
			// Nothing here
		}

		//---------------------------------------------
		// Class methods
		//---------------------------------------------

		/**
		* Store variable in the cache.
		*
		* @access  public
		* @param   string   Cache key
		* @param   mixed    The variable to store
		* @param   int      (optional) Time to live
		* @return  boolean
		*/

		public function write($key, $value, $ttl = 0)
		{
			$this->cache[$key] = $value;
			
			return true;
		}

		/**
		* Fetch variable from the cache.
		*
		* @access  public
		* @param   string  Cache key
		* @return  mixed
		*/

		public function read($key)
		{
			return isset($this->cache[$key]) ? $this->cache[$key] : false;
		}

		/**
		* Delete a variable from the cache.
		*
		* @access  public
		* @param   string   Cache key
		* @return  boolean
		*/

		public function delete($key)
		{
			if(isset($this->cache[$key]))
			{
				unset($this->cache[$key]);
				
				return true;
			}
			else
			{
				return false;
			}
		}

		/**
		* Clears the user cache.
		*
		* @access  public
		* @return  boolean
		*/

		public function clear()
		{
			$this->cache = array();
			
			return true;
		}
	}
}

/** -------------------- End of file --------------------**/