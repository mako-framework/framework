<?php

namespace mako\session;

/**
 * Session adapter.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2012 Frederic G. Østby
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
	 * @param   mixed   $data  (optional) Flash data
	 * @return  mixed
	 */

	public function flash($data = null)
	{
		if($data !== null)
		{
			$_SESSION[MAKO_APPLICATION_ID . '_flash'] = $data;
		}
		else
		{
			if(isset($_SESSION[MAKO_APPLICATION_ID . '_flash']))
			{
				$data = $_SESSION[MAKO_APPLICATION_ID . '_flash'];

				unset($_SESSION[MAKO_APPLICATION_ID . '_flash']);

				return $data;
			}
			else
			{
				return false;
			}
		}
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
	 * @param   boolean  $deleteOld  (optional)  Delete the session associated with the old id?
	 * @return  boolean
	 */

	public function regenerate($deleteOld = false)
	{
		return session_regenerate_id($deleteOld);
	}

	/**
	 *  Destroys all data registered to the session.
	 * 
	 * @access  public
	 * @return  boolean
	 */

	public function destroy()
	{
		return session_destroy();
	}
}

/** -------------------- End of file --------------------**/