<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response\senders;

use mako\http\Request;
use mako\http\Response;

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
	protected $statusCode;

	/**
	 * Constructor.
	 *
	 * @param string $location Location
	 * @param int    $statusCode   Status code
	 */
	public function __construct(string $location, int $statusCode = 302)
	{
		$this->location = $location;

		$this->statusCode = $statusCode;
	}

	/**
	 * Sets the HTTP status code.
	 *
	 * @param  int                                  $statusCode Status code
	 * @return \mako\http\response\senders\Redirect
	 */
	public function setStatus(int $statusCode): Redirect
	{
		$this->statusCode = $statusCode;

		return $this;
	}

	/**
	 * Sets the HTTP status code to 300.
	 *
	 * @return \mako\http\response\senders\Redirect
	 */
	public function multipleChoices(): Redirect
	{
		$this->statusCode = 300;

		return $this;
	}

	/**
	 * Sets the HTTP status code to 301.
	 *
	 * @return \mako\http\response\senders\Redirect
	 */
	public function movedPermanently(): Redirect
	{
		$this->statusCode = 301;

		return $this;
	}

	/**
	 * Sets the HTTP status code to 302.
	 *
	 * @return \mako\http\response\senders\Redirect
	 */
	public function found(): Redirect
	{
		$this->statusCode = 302;

		return $this;
	}

	/**
	 * Sets the HTTP status code to 303.
	 *
	 * @return \mako\http\response\senders\Redirect
	 */
	public function seeOther(): Redirect
	{
		$this->statusCode = 303;

		return $this;
	}

	/**
	 * Sets the HTTP status code to 304.
	 *
	 * @return \mako\http\response\senders\Redirect
	 */
	public function notModified(): Redirect
	{
		$this->statusCode = 304;

		return $this;
	}

	/**
	 * Sets the HTTP status code to 305.
	 *
	 * @return \mako\http\response\senders\Redirect
	 */
	public function useProxy(): Redirect
	{
		$this->statusCode = 305;

		return $this;
	}

	/**
	 * Sets the HTTP status code to 307.
	 *
	 * @return \mako\http\response\senders\Redirect
	 */
	public function temporaryRedirect(): Redirect
	{
		$this->statusCode = 307;

		return $this;
	}

	/**
	 * Sets the HTTP status code to 308.
	 *
	 * @return \mako\http\response\senders\Redirect
	 */
	public function permanentRedirect(): Redirect
	{
		$this->statusCode = 308;

		return $this;
	}

	/**
	 * Returns the HTTP status code.
	 *
	 * @return int
	 */
	public function getStatus(): int
	{
		return $this->statusCode;
	}

	/**
	 * {@inheritdoc}
	 */
	public function send(Request $request, Response $response): void
	{
		// Set status and location header

		$response->setStatus($this->statusCode);

		$response->getHeaders()->add('Location', $this->location);

		// Send headers

		$response->sendHeaders();
	}
}
