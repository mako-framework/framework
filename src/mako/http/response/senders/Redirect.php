<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response\senders;

use mako\http\exceptions\HttpException;
use mako\http\Request;
use mako\http\Response;

use function in_array;
use function vsprintf;

/**
 * Redirect response.
 */
class Redirect implements ResponseSenderInterface
{
	/**
	 * Moved permanently status code.
	 *
	 * @var int
	 */
	public const MOVED_PERMANENTLY = 301;

	/**
	 * Found status code.
	 *
	 * @var int
	 */
	public const FOUND = 302;

	/**
	 * See other status code.
	 *
	 * @var int
	 */
	public const SEE_OTHER = 303;

	/**
	 * Temporary redirect status code.
	 *
	 * @var int
	 */
	public const TEMPORARY_REDIRECT = 307;

	/**
	 * Permanent redirect status code.
	 *
	 * @var int
	 */
	public const PERMANENT_REDIRECT = 308;

	/**
	 * Supported redirect types.
	 *
	 * @var array
	 */
	public const SUPPORTED_STATUS_CODES =
	[
		self::MOVED_PERMANENTLY,
		self::FOUND,
		self::SEE_OTHER,
		self::TEMPORARY_REDIRECT,
		self::PERMANENT_REDIRECT,
	];

	/**
	 * Status code.
	 */
	protected int $statusCode;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $location,
		int $statusCode = self::FOUND
	)
	{
		$this->setStatus($statusCode);
	}

	/**
	 * Sets the HTTP status code.
	 */
	public function setStatus(int $statusCode): Redirect
	{
		if(!in_array($statusCode, self::SUPPORTED_STATUS_CODES))
		{
			throw new HttpException(vsprintf('Unsupported redirect status code [ %s ].', [$statusCode]));
		}

		$this->statusCode = $statusCode;

		return $this;
	}

	/**
	 * Sets the HTTP status code to 301.
	 */
	public function movedPermanently(): Redirect
	{
		$this->statusCode = self::MOVED_PERMANENTLY;

		return $this;
	}

	/**
	 * Sets the HTTP status code to 302.
	 */
	public function found(): Redirect
	{
		$this->statusCode = self::FOUND;

		return $this;
	}

	/**
	 * Sets the HTTP status code to 303.
	 */
	public function seeOther(): Redirect
	{
		$this->statusCode = self::SEE_OTHER;

		return $this;
	}

	/**
	 * Sets the HTTP status code to 307.
	 */
	public function temporaryRedirect(): Redirect
	{
		$this->statusCode = self::TEMPORARY_REDIRECT;

		return $this;
	}

	/**
	 * Sets the HTTP status code to 308.
	 */
	public function permanentRedirect(): Redirect
	{
		$this->statusCode = self::PERMANENT_REDIRECT;

		return $this;
	}

	/**
	 * Returns the HTTP status code.
	 */
	public function getStatus(): int
	{
		return $this->statusCode;
	}

	/**
	 * {@inheritDoc}
	 */
	public function send(Request $request, Response $response): void
	{
		// Set status and location header

		$response->setStatus($this->statusCode);

		$response->headers->add('Location', $this->location);

		// Send headers

		$response->sendHeaders();
	}
}
