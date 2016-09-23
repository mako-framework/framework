<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\http\response\senders;

use mako\http\Request;
use mako\http\Response;
use mako\http\response\senders\ResponseSenderInterface;

/**
 * Redirect response.
 *
 * @author  Frederic G. Østby
 */
class Redirect implements ResponseSenderInterface
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
	 * @param   int                                   $status  Status code
	 * @return  \mako\http\response\senders\Redirect
	 */
	public function status($status)
	{
		$this->status = $status;

		return $this;
	}

	/**
	 * Sets the status code to 300.
	 *
	 * @access  public
	 * @return  \mako\http\response\senders\Redirect
	 */
	public function multipleChoices()
	{
		$this->status = 300;

		return $this;
	}

	/**
	 * Sets the status code to 301.
	 *
	 * @access  public
	 * @return  \mako\http\response\senders\Redirect
	 */
	public function movedPermanently()
	{
		$this->status = 301;

		return $this;
	}

	/**
	 * Sets the status code to 302.
	 *
	 * @access  public
	 * @return  \mako\http\response\senders\Redirect
	 */
	public function found()
	{
		$this->status = 302;

		return $this;
	}

	/**
	 * Sets the status code to 303.
	 *
	 * @access  public
	 * @return  \mako\http\response\senders\Redirect
	 */
	public function seeOther()
	{
		$this->status = 303;

		return $this;
	}

	/**
	 * Sets the status code to 304.
	 *
	 * @access  public
	 * @return  \mako\http\response\senders\Redirect
	 */
	public function notModified()
	{
		$this->status = 304;

		return $this;
	}

	/**
	 * Sets the status code to 305.
	 *
	 * @access  public
	 * @return  \mako\http\response\senders\Redirect
	 */
	public function useProxy()
	{
		$this->status = 305;

		return $this;
	}

	/**
	 * Sets the status code to 307.
	 *
	 * @access  public
	 * @return  \mako\http\response\senders\Redirect
	 */
	public function temporaryRedirect()
	{
		$this->status = 307;

		return $this;
	}

	/**
	 * Sets the status code to 308.
	 *
	 * @access  public
	 * @return  \mako\http\response\senders\Redirect
	 */
	public function permanentRedirect()
	{
		$this->status = 308;

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