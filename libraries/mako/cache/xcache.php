<?php

namespace mako\cache
{
	use \Mako;
	use \RuntimeException;
	
	/**
	* XCache adapter.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class XCache extends \mako\cache\Adapter
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------
		
		/**
		* XCache username.
		*/

		protected $username;
		
		/**
		* XCache password.
		*/
		
		protected $password;

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
			
			$this->username = $config['username'];
			
			$this->password = $config['password'];
			
			if(function_exists('xcache_get') === false)
			{
				throw new RuntimeException(__CLASS__ . ": XCache is not available.");
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
			return xcache_set("{$this->identifier}_{$key}", serialize($value), $ttl);
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
			return unserialize(xcache_get("{$this->identifier}_{$key}"));
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
			return xcache_unset("{$this->identifier}_{$key}");
		}

		/**
		* Clears the user cache.
		*
		* @access  public
		* @return  boolean
		*/

		public function clear()
		{
			$cleared = true;

			// Set XCache password

			$tempUsername = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : false;
			$tempPassword = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : false;

			$_SERVER['PHP_AUTH_USER'] = $this->username;
			$_SERVER['PHP_AUTH_PW']   = $this->password;

			// Clear Cache

			$cacheCount = xcache_count(XC_TYPE_VAR);

			for($i = 0; $i < $cacheCount; $i++)
			{
				if(@xcache_clear_cache(XC_TYPE_VAR, $i) === false)
				{
					$cleared = false;
					break;
				}
			}

			// Reset PHP_AUTH username/password

			if($tempUsername !== false)
			{
				$_SERVER['PHP_AUTH_USER'] = $tempUsername;
			}
			else
			{
				unset($_SERVER['PHP_AUTH_USER']);
			}

			if($tempPassword !== false)
			{
				$_SERVER['PHP_AUTH_PW'] = $tempPassword;
			}
			else
			{
				unset($_SERVER['PHP_AUTH_PW']);
			}

			// Return result

			return $cleared;
		}
	}
}

/** -------------------- End of file --------------------**/