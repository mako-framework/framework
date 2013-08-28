<?php

namespace mako\session;

/**
 * Session adapter.
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
	 * Configuration.
	 * 
	 * @var array
	 */

	protected $config;

	/**
	 * Flashdata.
	 * 
	 * @var array
	 */

	protected $flashdata = array();

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
		$this->config = $config;
	}

	/**
	 * Destructor.
	 * 
	 * @access  public
	 */

	public function __destruct()
	{
		unset($_SESSION['mako:flashdata']);

		$_SESSION['mako:flashdata'] = $this->flashdata;
	}
	
	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Store a value in the session.
	 * 
	 * @access  public
	 * @param   string  $key    Session key
	 * @param   mixed   $value  Session data
	 */

	public function remember($key, $value)
	{
		$_SESSION[$key] = $value;
	}

	/**
	 * Removes a value from the session.
	 * 
	 * @access  public
	 * @param   string  $key  Session key
	 */

	public function forget($key)
	{
		unset($_SESSION[$key]);
	}

	/**
	 * Returns TRUE if key exists in the session and FALSE if not.
	 * 
	 * @access  public
	 * @param   string   $key  Session key
	 * @return  boolean
	 */

	public function has($key)
	{
		return isset($_SESSION[$key]);
	}

	/**
	 * Returns a value from the session.
	 * 
	 * @access  public
	 * @param   string  $key      Session key
	 * @param   mixed   $default  (optional) Default value
	 * @return  mixed
	 */

	public function get($key, $default = null)
	{
		return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
	}

	/**
	 * Sets or gets flash data from the session.
	 *
	 * @access  public
	 * @param   string  $key   Session flash key
	 * @param   mixed   $data  (optional) Flash data
	 * @return  mixed
	 */

	public function flash($key, $data = null)
	{
		if($data === null)
		{
			return isset($_SESSION['mako:flashdata'][$key]) ? $_SESSION['mako:flashdata'][$key] : false;
		}
		else
		{
			$this->flashdata[$key] = $data;
		}
	}

	/**
	 * Extends the lifetime of the flash data by one request.
	 * 
	 * @access  public
	 * @param   array   $keys  (optional) Keys to preserve
	 */

	public function reflash(array $keys = array())
	{
		$flashdata = empty($keys) ? $_SESSION['mako:flashdata'] : array_intersect_key($_SESSION['mako:flashdata'], array_flip($keys));

		$this->flashdata = array_merge($this->flashdata, $flashdata);
	}

	/**
	 * Clears all session data.
	 * 
	 * @access  public
	 */

	public function clear()
	{
		$_SESSION = array();
	}

	/**
	 * Returns the session id.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function id()
	{
		return session_id();
	}

	/**
	 * Regenerates the session id.
	 * 
	 * @access  public
	 * @param   boolean  $deleteOld  (optional)  Delete the session data associated with the old id?
	 * @return  boolean
	 */

	public function regenerate($deleteOld = true)
	{
		return session_regenerate_id($deleteOld);
	}

	/**
	 * Destroys all data registered to the session.
	 * 
	 * @access  public
	 * @return  boolean
	 */

	public function destroy()
	{
		return session_destroy();
	}
}

/** -------------------- End of file -------------------- **/