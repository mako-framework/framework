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
use Override;

use function in_array;
use function is_int;
use function sprintf;

/**
 * Redirect response.
 */
class Redirect implements ResponseSenderInterface
{
	/**
	 * Moved permanently status code.
	 */
	public const Status MOVED_PERMANENTLY = Status::MOVED_PERMANENTLY;

	/**
	 * Found status code.
	 */
	public const Status FOUND = Status::FOUND;

	/**
	 * See other status code.
	 */
	public const Status SEE_OTHER = Status::SEE_OTHER;

	/**
	 * Temporary redirect status code.
	 */
	public const Status TEMPORARY_REDIRECT = Status::TEMPORARY_REDIRECT;

	/**
	 * Permanent redirect status code.
	 */
	public const Status PERMANENT_REDIRECT = Status::PERMANENT_REDIRECT;

	/**
	 * Supported redirect types.
	 */
	public const array SUPPORTED_STATUS_CODES = [
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
	) {
		$this->setStatus($status);
	}

	/**
	 * Sets the HTTP status code.
	 *
	 * @return $this
	 */
	public function setStatus(int|Status $status): Redirect
	{
		$status = is_int($status) ? Status::from($status) : $status;

		if (!in_array($status, self::SUPPORTED_STATUS_CODES)) {
			throw new HttpException(sprintf('Unsupported redirect status code [ %s ].', $status->value));
		}

		$this->status = $status;

		return $this;
	}

	/**
	 * Sets the HTTP status code to 301.
	 *
	 * @return $this
	 */
	public function movedPermanently(): Redirect
	{
		$this->status = Status::MOVED_PERMANENTLY;

		return $this;
	}

	/**
	 * Sets the HTTP status code to 302.
	 *
	 * @return $this
	 */
	public function found(): Redirect
	{
		$this->status = Status::FOUND;

		return $this;
	}

	/**
	 * Sets the HTTP status code to 303.
	 *
	 * @return $this
	 */
	public function seeOther(): Redirect
	{
		$this->status = Status::SEE_OTHER;

		return $this;
	}

	/**
	 * Sets the HTTP status code to 307.
	 *
	 * @return $this
	 */
	public function temporaryRedirect(): Redirect
	{
		$this->status = Status::TEMPORARY_REDIRECT;

		return $this;
	}

	/**
	 * Sets the HTTP status code to 308.
	 *
	 * @return $this
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
	#[Override]
	public function send(Request $request, Response $response): void
	{
		// Set status and location header

		$response->setStatus($this->status);

		$response->headers->add('Location', $this->location);

		// Send headers

		$response->sendHeaders();
	}
}
