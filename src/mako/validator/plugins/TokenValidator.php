<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\validator\plugins;

use mako\session\Session;
use mako\validator\plugins\ValidatorPlugin;

/**
 * Token validator plugin.
 *
 * @author  Frederic G. Østby
 */

class TokenValidator extends ValidatorPlugin
{
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

	/**
	 * Validates a token.
	 *
	 * @access  public
	 * @param   string  $input Input
	 * @return  boolean
	 */

	public function validate($input)
	{
		return $this->session->validateToken($input);
	}
}