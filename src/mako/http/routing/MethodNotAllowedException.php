<?php

namespace mako\http\routing;

use \Exception;

/**
 * Method not allowed exception.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class MethodNotAllowedException extends \mako\http\RequestException
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Allowed methods.
	 * 
	 * @var array
	 */
	 
	 protected $allowedMethods;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   array      $allowedMethods  Allowed methods
	 * @param   string     $message         Exception message
	 * @param   \Exception $previous        (optional) Previous exception
	 */

	public function __construct(array $allowedMethods, $message = null, Exception $previous = null)
	{
		$this->allowedMethods = $allowedMethods;

		parent::__construct(405, $message, $previous);
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns the allowed methods.
	 * 
	 * @access  public
	 * @return  array
	 */

	public function getAllowedMethods()
	{
		return $this->allowedMethods;
	}
}

/** -------------------- End of file -------------------- **/