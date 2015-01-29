<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use mako\cache\stores\StoreInterface;

/**
 * XCache store.
 *
 * @author  Frederic G. Østby
 */

class XCache implements StoreInterface
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
	 * @param   string  $username  Username
	 * @param   string  $password  Password
	 */

	public function __construct($username = null, $password = null)
	{
		$this->username = $username;

		$this->password = $password;
	}

	/**
	 * {@inheritdoc}
	 */

	public function put($key, $data, $ttl = 0)
	{
		return xcache_set($key, serialize($data), $ttl);
	}

	/**
	 * {@inheritdoc}
	 */

	public function has($key)
	{
		return xcache_isset($key);
	}

	/**
	 * {@inheritdoc}
	 */

	public function get($key)
	{
		return unserialize(xcache_get($key));
	}

	/**
	 * {@inheritdoc}
	 */

	public function remove($key)
	{
		return xcache_unset($key);
	}

	/**
	 * {@inheritdoc}
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