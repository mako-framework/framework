<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\validator\plugins;

use \mako\session\Session;

/**
 * Token validator plugin.
 *
 * @author  Frederic G. Østby
 */

class TokenValidator extends \mako\validator\plugins\ValidatorPlugin implements \mako\validator\plugins\ValidatorPluginInterface
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Rule name.
	 * 
	 * @var string
	 */

	protected $ruleName = 'token';

	/**
	 * Session instance.
	 * 
	 * @var \mako\session\Session
	 */

	protected $session;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\session\Session  $session  Session instance
	 */

	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Validator.
	 * 
	 * @access  public
	 * @param   string   $input       Input
	 * @param   array    $parameters  Parameters
	 * @return  boolean
	 */

	public function validate($input, $parameters)
	{
		return $this->session->validateToken($input);
	}
}

