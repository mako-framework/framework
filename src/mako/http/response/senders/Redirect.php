<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response\senders;

use mako\http\Request;
use mako\http\Response;
use mako\http\response\senders\ResponseSenderInterface;

/**
 * Redirect response.
 *
 * @author Frederic G. Østby
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
	 * @param string $location Location
	 */
	public function __construct(string $location)
	{
		$this->location = $location;
	}

	/**
	 * Sets the status code.
	 *
	 * @param  int                                  $status Status code
	 * @return \mako\http\response\senders\Redirect
	 */
	public function status(int $status): Redirect
	{
		$this->status = $status;

		return $this;
	}

	/**
	 * Sets the status code to 300.
	 *
	 * @return \mako\http\response\senders\Redirect
	 */
	public function multipleChoices(): Redirect
	{
		$this->status = 300;

		return $this;
	}

	/**
	 * Sets the status code to 301.
	 *
	 * @return \mako\http\response\senders\Redirect
	 */
	public function movedPermanently(): Redirect
	{
		$this->status = 301;

		return $this;
	}

	/**
	 * Sets the status code to 302.
	 *
	 * @return \mako\http\response\senders\Redirect
	 */
	public function found(): Redirect
	{
		$this->status = 302;

		return $this;
	}

	/**
	 * Sets the status code to 303.
	 *
	 * @return \mako\http\response\senders\Redirect
	 */
	public function seeOther(): Redirect
	{
		$this->status = 303;

		return $this;
	}

	/**
	 * Sets the status code to 304.
	 *
	 * @return \mako\http\response\senders\Redirect
	 */
	public function notModified(): Redirect
	{
		$this->status = 304;

		return $this;
	}

	/**
	 * Sets the status code to 305.
	 *
	 * @return \mako\http\response\senders\Redirect
	 */
	public function useProxy(): Redirect
	{
		$this->status = 305;

		return $this;
	}

	/**
	 * Sets the status code to 307.
	 *
	 * @return \mako\http\response\senders\Redirect
	 */
	public function temporaryRedirect(): Redirect
	{
		$this->status = 307;

		return $this;
	}

	/**
	 * Sets the status code to 308.
	 *
	 * @return \mako\http\response\senders\Redirect
	 */
	public function permanentRedirect(): Redirect
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

		$response->getHeaders()->add('Location', $this->location);

		// Send headers

		$response->sendHeaders();
	}
}
