<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\web;

use mako\error\handlers\ProvidesExceptionIdInterface;
use mako\http\exceptions\HttpStatusException;
use mako\http\exceptions\ProvidesHeadersInterface;
use mako\http\Response;
use mako\http\response\Status;
use mako\http\traits\ContentNegotiationTrait;
use mako\utility\UUID;
use Override;
use Throwable;

/**
 * Base handler.
 */
abstract class Handler implements ProvidesExceptionIdInterface
{
	use ContentNegotiationTrait;

	/**
	 * Exception id.
	 */
	protected string $exceptionId;

	/**
	 * Generates an exception id.
	 */
	protected function generateExceptionId(): string
	{
		return UUID::v4();
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getExceptionId(): string
	{
		return $this->exceptionId;
	}

	/**
	 * Returns the status that we should send.
	 */
	protected function getHttpStatus(Throwable $exception): Status
	{
		return ($exception instanceof HttpStatusException) ? $exception->getStatus() : Status::INTERNAL_SERVER_ERROR;
	}

	/**
	 * Sends response and adds any aditional headers.
	 */
	protected function sendResponse(Response $response, Throwable $exception): void
	{
		if ($exception instanceof ProvidesHeadersInterface) {
			foreach ($exception->getHeaders() as $name => $value) {
				$response->headers->add($name, $value);
			}
		}

		$response->send();
	}
}
