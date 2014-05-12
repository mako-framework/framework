<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache\stores;

/**
 * XCache store.
 *
 * @author  Frederic G. Østby
 */

class XCache implements \mako\cache\stores\StoreInterface
{
	/**
	 * XCache username.
	 *
	 * @var string
	 */

	protected $username;
	
	/**
	 * XCache password.
	 *
	 * @var string
	 */
	
	protected $password;

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   string  $username  (optional) Username
	 * @param   string  $password  (optional) Password
	 */

	public function __construct($username = null, $password = null)
	{
		$this->username = $username;

		$this->password = $password;
	}

	/**
	 * Store data in the cache.
	 *
	 * @access  public
	 * @param   string   $key    Cache key
	 * @param   mixed    $data   The data to store
	 * @param   int      $ttl    (optional) Time to live
	 * @return  boolean
	 */

	public function put($key, $data, $ttl = 0)
	{
		return xcache_set($key, serialize($data), $ttl);
	}

	/**
	 * Returns TRUE if the cache key exists and FALSE if not.
	 * 
	 * @access  public
	 * @param   string   $key  Cache key
	 * @return  boolean
	 */

	public function has($key)
	{
		return xcache_isset($key);
	}

	/**
	 * Fetch data from the cache.
	 *
	 * @access  public
	 * @param   string  $key  Cache key
	 * @return  mixed
	 */

	public function get($key)
	{
		return unserialize(xcache_get($key));
	}

	/**
	 * Delete data from the cache.
	 *
	 * @access  public
	 * @param   string   $key  Cache key
	 * @return  boolean
	 */

	public function remove($key)
	{
		return xcache_unset($key);
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
		$tempPassword = isset($_SERVER['PHP_AUTH_PW'])   ? $_SERVER['PHP_AUTH_PW']   : false;

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