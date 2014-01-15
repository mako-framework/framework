<?php

namespace mako\http\responses;

use \mako\http\Request;
use \mako\http\Response;

/**
 * Redirect response.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

Class Redirect implements \mako\http\responses\ResponseContainerInterface
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Location.
	 * 
	 * @var string
	 */

	protected $location;

	/**
	 * Status code.
	 * 
	 * @var int
	 */

	protected $status = 302;

	/**
	 * Flash the request data?
	 * 
	 * @var boolean
	 */

	protected $flashRequestData = false;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   string  $location  Location
	 */

	public function __construct($location)
	{
		$this->location = $location;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Sets the status code.
	 * 
	 * @access  public
	 * @param   int                            $status  Status code
	 * @return  \mako\http\responses\Redirect
	 */

	public function status($status)
	{
		$this->status = $status;

		return $this;
	}

	/**
	 * Sends the response.
	 * 
	 * @access  public
	 * @param   \mako\http\Request   $request  Request instance
	 * @param   \mako\http\Response  $response  Response instance
	 */

	public function send(Request $request, Response $response)
	{
		// Set status and location header

		$response->status($this->status);

		$response->header('Location', $this->location);

		// Send headers

		$response->sendHeaders();
	}
}

/** -------------------- End of file -------------------- **/