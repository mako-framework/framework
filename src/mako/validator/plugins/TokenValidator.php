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
	 * Validator.
	 *
	 * @access  public
	 * @param   string   $input       Input field value
	 * @param   array    $parameters  Parameters
	 * @param   array    $data        Input data array
	 * @return  boolean
	 */

	public function validate($input, $parameters, $data)
	{
		return $this->session->validateToken($input);
	}
}