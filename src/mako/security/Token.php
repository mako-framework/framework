<?php

namespace mako\security;

use \mako\Session;

/**
 * Generate and validate security tokens.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Token
{
	//---------------------------------------------
	// Class properties
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
	 * Returns random security token.
	 *
	 * @access  public
	 * @return  string
	 */
	
	public static function generate()
	{
		$tokens = Session::get('mako:tokens', array());

		if(!empty($tokens))
		{
			$tokens = array_slice($tokens, 0, (static::MAX_TOKENS - 1)); // Only store MAX_TOKENS tokens per session
		}

		$token = md5(uniqid('token', true));

		array_unshift($tokens, $token);

		Session::remember('mako:tokens', $tokens);

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
		$tokens = Session::get('mako:tokens', array());

		$key = array_search($token, $tokens);

		if($key !== false)
		{
			unset($tokens[$key]);

			Session::remember('mako:tokens', $tokens);

			return true;
		}

		return false;
	}
}

/** -------------------- End of file -------------------- **/