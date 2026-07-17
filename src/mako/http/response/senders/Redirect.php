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
use mako\http\response\StatusInterface;
use Override;

use function sprintf;

/**
 * Redirect response.
 */
class Redirect implements ResponseSenderInterface
{
	/**
	 * Moved permanently status code.
	 */
	public const Status MOVED_PERMANENTLY = Status::MovedPermanently;

	/**
	 * Found status code.
	 */
	public const Status FOUND = Status::Found;

	/**
	 * See other status code.
	 */
	public const Status SEE_OTHER = Status::SeeOther;

	/**
	 * Temporary redirect status code.
	 */
	public const Status TEMPORARY_REDIRECT = Status::TemporaryRedirect;

	/**
	 * Permanent redirect status code.
	 */
	public const Status PERMANENT_REDIRECT = Status::PermanentRedirect;

	/**
	 * Status code.
	 */
	protected StatusInterface $status;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $location,
		StatusInterface $status = Status::Found
	) {
		$this->setStatus($status);
	}

	/**
	 * Sets the HTTP status code.
	 *
	 * @return $this
	 */
	public function setStatus(StatusInterface $status): Redirect
	{
		if (!$status->isRedirection()) {
			throw new HttpException(sprintf('Unsupported redirect status code [ %s ].', $status->getCode()));
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
		$this->status = Status::MovedPermanently;

		return $this;
	}

	/**
	 * Sets the HTTP status code to 302.
	 *
	 * @return $this
	 */
	public function found(): Redirect
	{
		$this->status = Status::Found;

		return $this;
	}

	/**
	 * Sets the HTTP status code to 303.
	 *
	 * @return $this
	 */
	public function seeOther(): Redirect
	{
		$this->status = Status::SeeOther;

		return $this;
	}

	/**
	 * Sets the HTTP status code to 307.
	 *
	 * @return $this
	 */
	public function temporaryRedirect(): Redirect
	{
		$this->status = Status::TemporaryRedirect;

		return $this;
	}

	/**
	 * Sets the HTTP status code to 308.
	 *
	 * @return $this
	 */
	public function permanentRedirect(): Redirect
	{
		$this->status = Status::PermanentRedirect;

		return $this;
	}

	/**
	 * Returns the HTTP status.
	 */
	public function getStatus(): StatusInterface
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
