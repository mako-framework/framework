<?php

namespace mako\cache
{
	use \mako\cache\Exception as CacheException;
	
	/**
	* Zend Data (disk) Cache adapter.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class ZendDisk extends \mako\cache\Adapter
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
			
			if(function_exists('zend_disk_cache_fetch') === false)
			{
				throw new CacheException(__CLASS__.': Zend Data Cache is not available.');
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
			return zend_disk_cache_store("{$this->identifier}_{$key}", $value, $ttl);
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
			return zend_disk_cache_fetch("{$this->identifier}_{$key}");
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
			return zend_disk_cache_delete("{$this->identifier}_{$key}");
		}

		/**
		* Clears the user cache.
		*
		* @access  public
		* @return  boolean
		*/

		public function clear()
		{
			return zend_disk_cache_clear();
		}
	}
}

/** -------------------- End of file --------------------**/