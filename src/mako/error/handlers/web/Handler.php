<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\web;

use mako\error\handlers\ProvidesExceptionIdInterface;
use mako\http\exceptions\HttpStatusException;
use mako\http\exceptions\MethodNotAllowedException;
use mako\http\Response;
use mako\http\traits\ContentNegotiationTrait;
use mako\utility\UUID;
use Throwable;

use function implode;

/**
 * Base handler.
 */
abstract class Handler implements ProvidesExceptionIdInterface
{
	use ContentNegotiationTrait;

	/**
	 * Exception id.
	 *
	 * @var string
	 */
	protected $exceptionId;

	/**
	 * Generates an exception id.
	 *
	 * @return string
	 */
	protected function generateExceptionId(): string
	{
		return UUID::v4();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getExceptionId(): string
	{
		return $this->exceptionId;
	}

	/**
	 * Returns the status code that we should send.
	 *
	 * @param  \Throwable $exception Exception
	 * @return int
	 */
	protected function getStatusCode(Throwable $exception): int
	{
		return ($exception instanceof HttpStatusException) ? $exception->getCode() : 500;
	}

	/**
	 * Sends response and adds any aditional headers.
	 *
	 * @param \mako\http\Response $response  Response
	 * @param \Throwable          $exception Exception
	 */
	protected function sendResponse(Response $response, Throwable $exception): void
	{
		if($exception instanceof MethodNotAllowedException)
		{
			$response->getHeaders()->add('Allow', implode(',', $exception->getAllowedMethods()));
		}

		$response->send();
	}
}
