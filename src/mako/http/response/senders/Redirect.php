<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response\senders;

use mako\http\exceptions\HttpException;
use mako\http\Request;
use mako\http\Response;
use mako\http\response\Status;

use function in_array;
use function is_int;
use function vsprintf;

/**
 * Redirect response.
 */
class Redirect implements ResponseSenderInterface
{
	/**
	 * Moved permanently status code.
	 *
	 * @var \mako\http\response\Status
	 */
	public const MOVED_PERMANENTLY = Status::MOVED_PERMANENTLY;

	/**
	 * Found status code.
	 *
	 * @var \mako\http\response\Status
	 */
	public const FOUND = Status::FOUND;

	/**
	 * See other status code.
	 *
	 * @var \mako\http\response\Status
	 */
	public const SEE_OTHER = Status::SEE_OTHER;

	/**
	 * Temporary redirect status code.
	 *
	 * @var \mako\http\response\Status
	 */
	public const TEMPORARY_REDIRECT = Status::TEMPORARY_REDIRECT;

	/**
	 * Permanent redirect status code.
	 *
	 * @var \mako\http\response\Status
	 */
	public const PERMANENT_REDIRECT = Status::PERMANENT_REDIRECT;

	/**
	 * Supported redirect types.
	 *
	 * @var array
	 */
	public const SUPPORTED_STATUS_CODES =
	[
		Status::MOVED_PERMANENTLY,
		Status::FOUND,
		Status::SEE_OTHER,
		Status::TEMPORARY_REDIRECT,
		Status::PERMANENT_REDIRECT,
	];

	/**
	 * Status code.
	 */
	protected Status $status;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $location,
		int|Status $status = Status::FOUND
	)
	{
		$this->setStatus($status);
	}

	/**
	 * Sets the HTTP status code.
	 */
	public function setStatus(int|Status $status): Redirect
	{
		$status = is_int($status) ? Status::from($status) : $status;

		if(!in_array($status, self::SUPPORTED_STATUS_CODES))
		{
			throw new HttpException(vsprintf('Unsupported redirect status code [ %s ].', [$status->value]));
		}

		$this->status = $status;

		return $this;
	}

	/**
	 * Sets the HTTP status code to 301.
	 */
	public function movedPermanently(): Redirect
	{
		$this->status = Status::MOVED_PERMANENTLY;

		return $this;
	}

	/**
	 * Sets the HTTP status code to 302.
	 */
	public function found(): Redirect
	{
		$this->status = Status::FOUND;

		return $this;
	}

	/**
	 * Sets the HTTP status code to 303.
	 */
	public function seeOther(): Redirect
	{
		$this->status = Status::SEE_OTHER;

		return $this;
	}

	/**
	 * Sets the HTTP status code to 307.
	 */
	public function temporaryRedirect(): Redirect
	{
		$this->status = Status::TEMPORARY_REDIRECT;

		return $this;
	}

	/**
	 * Sets the HTTP status code to 308.
	 */
	public function permanentRedirect(): Redirect
	{
		$this->status = Status::PERMANENT_REDIRECT;

		return $this;
	}

	/**
	 * Returns the HTTP status.
	 */
	public function getStatus(): Status
	{
		return $this->status;
	}

	/**
	 * {@inheritDoc}
	 */
	public function send(Request $request, Response $response): void
	{
		// Set status and location header

		$response->setStatus($this->status);

		$response->headers->add('Location', $this->location);

		// Send headers

		$response->sendHeaders();
	}
}
