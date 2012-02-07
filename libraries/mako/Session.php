<?php

namespace mako;

use \mako\Mako;
use \RuntimeException;

/**
* Session class.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Session
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------
	
	// Nothing here

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	* Protected constructor since this is a static class.
	*
	* @access  protected
	*/

	protected function __construct()
	{
		// Nothing here
	}
	
	//---------------------------------------------
	// Class methods
	//---------------------------------------------
	
	/**
	* Starts a session using the chosen type of storage.
	*
	* @param   string  (optional) Session configuration name
	*/
	
	public static function start($name = null)
	{
		$config = Mako::config('session');

		$name = ($name === null) ? $config['default'] : $name;

		if(isset($config['configurations'][$name]) === false)
		{
			throw new RuntimeException(vsprintf("%s(): '%s' has not been defined in the session configuration.", array(__METHOD__, $name)));
		}

		$type = $config['configurations'][$name]['type'];

		if(strtolower($type) !== 'native')
		{
			$class = '\mako\session\\' . $type;

			$handler = new $class($config['configurations'][$name]);

			session_set_save_handler
			(
				array($handler, 'open'), 
				array($handler, 'close'), 
				array($handler, 'read'), 
				array($handler, 'write'), 
				array($handler, 'destroy'), 
				array($handler, 'gc')
			);
		}

		session_start();
	}

	/**
	* Sets or gets flash data.
	*
	* @access  public
	* @param   mixed   (optional) Flash data
	* @return  mixed
	*/

	public static function flash($data = null)
	{
		if(session_id() === '')
		{
			Session::start();
		}

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
}

/** -------------------- End of file --------------------**/