<?php

namespace mako\cache;

use \RuntimeException;

/**
 * XCache adapter.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class XCache extends \mako\cache\Adapter
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------
	
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

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   array   $config  Configuration
	 */

	public function __construct(array $config)
	{
		parent::__construct($config['identifier']);
		
		$this->username = $config['username'];
		
		$this->password = $config['password'];
		
		if(function_exists('xcache_get') === false)
		{
			throw new RuntimeException(vsprintf("%s(): XCache is not available.", array(__METHOD__)));
		}
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Store variable in the cache.
	 *
	 * @access  public
	 * @param   string   $key    Cache key
	 * @param   mixed    $value  The variable to store
	 * @param   int      $ttl    (optional) Time to live
	 * @return  boolean
	 */

	public function write($key, $value, $ttl = 0)
	{
		return xcache_set($this->identifier . $key, serialize($value), $ttl);
	}

	/**
	 * Fetch variable from the cache.
	 *
	 * @access  public
	 * @param   string  $key  Cache key
	 * @return  mixed
	 */

	public function read($key)
	{
		return unserialize(xcache_get($this->identifier . $key));
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
		return xcache_isset($this->identifier . $key);
	}

	/**
	 * Increases a stored number. Will return the incremented value on success and FALSE on failure.
	 * 
	 * @access  public
	 * @param   string  $key      Cache key
	 * @param   int     $ammount  (optional) Ammoun that the number should be increased by
	 * @return  mixed
	 */

	public function increment($key, $ammount = 1)
	{
		return xcache_inc($this->identifier . $key, $ammount);
	}

	/**
	 * Decrements a stored number. Will return the decremented value on success and FALSE on failure.
	 * 
	 * @access  public
	 * @param   string  $key      Cache key
	 * @param   int     $ammount  (optional) Ammoun that the number should be decremented by
	 * @return  mixed
	 */

	public function decrement($key, $ammount = 1)
	{
		return xcache_dec($this->identifier . $key, $ammount);
	}

	/**
	 * Delete a variable from the cache.
	 *
	 * @access  public
	 * @param   string   $key  Cache key
	 * @return  boolean
	 */

	public function delete($key)
	{
		return xcache_unset($this->identifier . $key);
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

/** -------------------- End of file -------------------- **/