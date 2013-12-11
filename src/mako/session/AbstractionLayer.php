<?php

namespace mako\session;

use \SessionHandlerInterface;
use \mako\session\handlers\Native;

/**
 * Session abstraction layer.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class AbstractionLayer
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Session handler.
	 * 
	 * @var \SessionHandlerInterface
	 */

	protected $handler;

	/**
	 * Flashdata.
	 * 
	 * @var array
	 */

	protected $flashdata = [];

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \SessionHandlerInterface  $handler      Session handler
	 * @param   string                    $sessionName  Session name
	 */

	public function __construct(SessionHandlerInterface $handler, $sessionName)
	{
		$this->handler = $handler;

		// Set the session handler, set the session name and start the session

		session_set_save_handler($handler, false);

		session_name($sessionName);

		session_start();

		// Fetch flash data if there is any and unset it

		if(isset($_SESSION['mako:flashdata']))
		{
			$this->flashdata = $_SESSION['mako:flashdata'];

			unset($_SESSION['mako:flashdata']);
		}
	}

	/**
	 * Destructor.
	 * 
	 * @access  public
	 */

	public function __destruct()
	{
		// Write and close session

		session_write_close();
		
		// Fixes issue with Debian and Ubuntu session garbage collection

		if(mt_rand(1, 100) === 100 && !($this->handler instanceof Native))
		{
			$this->handler->gc(ini_get('session.gc_maxlifetime'));
		}
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
			return isset($this->flashdata[$key]) ? $this->flashdata[$key] : false;
		}
		else
		{
			$_SESSION['mako:flashdata'][$key] = $data;
		}
	}

	/**
	 * Extends the lifetime of the flash data by one request.
	 * 
	 * @access  public
	 * @param   array   $keys  (optional) Keys to preserve
	 */

	public function reflash(array $keys = [])
	{
		$flashdata = empty($keys) ? $this->flashdata : array_intersect_key($this->flashdata, array_flip($keys));

		$_SESSION['mako:flashdata'] = array_merge(isset($_SESSION['mako:flashdata']) ? $_SESSION['mako:flashdata'] : [] , $flashdata);
	}

	/**
	 * Clears all session data.
	 * 
	 * @access  public
	 */

	public function clear()
	{
		$_SESSION = [];
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