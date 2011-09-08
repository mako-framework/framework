<?php

namespace mako\cache
{
	use \mako\cache\Exception as CacheException;
	
	/**
	* Alternative PHP Cache adapter.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class APC extends \mako\cache\Core
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------

		// Nothing here

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
			parent::__construct($config['identifier']);
			
			if(function_exists('apc_fetch') === false)
			{
				throw new CacheException(__CLASS__.': APC is not available.');
			}
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
			return apc_store("{$this->identifier}_{$key}", $value, $ttl);
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
			return apc_fetch("{$this->identifier}_{$key}");
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
			return apc_delete("{$this->identifier}_{$key}");
		}

		/**
		* Clears the user cache.
		*
		* @access  public
		* @return  boolean
		*/

		public function clear()
		{
			return apc_clear_cache('user');
		}
	}
}

/** -------------------- End of file --------------------**/