<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\http\responses;

use mako\http\Request;
use mako\http\Response;
use mako\http\responses\ResponseContainerInterface;

/**
 * Redirect response.
 *
 * @author  Frederic G. Østby
 */

class Redirect implements ResponseContainerInterface
{
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
	 * {@inheritdoc}
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