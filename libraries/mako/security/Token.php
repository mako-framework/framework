<?php

namespace mako\security;

use \mako\Session;

/**
* Generate and validate security tokens.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Token
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------
	
	/**
	* Maximum number of tokens stored per session.
	*
	* @var int
	*/
	
	const MAX_TOKENS = 20;
	
	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	* Protected constructor since this is a static class.
	*/

	protected function __construct()
	{
		// Nothing here
	}
	
	//---------------------------------------------
	// Class methods
	//---------------------------------------------
	
	/**
	* Starts session if it doesn't exist.
	*
	* @access  protected
	*/
	
	protected static function sessionStart()
	{
		if(session_id() === '')
		{
			Session::start();
		}
	}
	
	/**
	* Returns random security token.
	*
	* @access  public
	* @return  string
	*/
	
	public static function generate()
	{
		static::sessionStart();
		
		if(!isset($_SESSION[MAKO_APPLICATION_ID . '_token']))
		{
			$_SESSION[MAKO_APPLICATION_ID . '_token'] = array();	
		}
		else
		{
			$_SESSION[MAKO_APPLICATION_ID . '_token'] = array_slice($_SESSION[MAKO_APPLICATION_ID . '_token'], 0, (static::MAX_TOKENS - 1)); // Only store MAX_TOKENS tokens per session
		}
		
		$token = md5(uniqid('token', true));
		
		array_unshift($_SESSION[MAKO_APPLICATION_ID . '_token'], $token);
		
		return $token;
	}
	
	/**
	* Validates security token.
	*
	* @access  public
	* @param   string   $token  Security token
	* @return  boolean
	*/
	
	public static function validate($token)
	{
		static::sessionStart();
		
		$key = array_search($token, $_SESSION[MAKO_APPLICATION_ID . '_token']);
		
		if($key !== false)
		{
			unset($token, $_SESSION[MAKO_APPLICATION_ID . '_token'][$key]);
			
			return true;
		}
		
		return false;
	}
}

/** -------------------- End of file --------------------**/