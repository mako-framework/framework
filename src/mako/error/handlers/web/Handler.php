<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\web;

use mako\http\exceptions\HttpException;
use mako\http\exceptions\MethodNotAllowedException;
use mako\http\Response;
use mako\http\traits\ContentNegotiationTrait;
use Throwable;

use function implode;

/**
 * Base handler.
 *
 * @author Frederic G. Østby
 */
abstract class Handler
{
	use ContentNegotiationTrait;

	/**
	 * Returns the status code that we should send.
	 *
	 * @param  \Throwable $exception Exception
	 * @return int
	 */
	protected function getStatusCode(Throwable $exception): int
	{
		return ($exception instanceof HttpException) ? $exception->getCode() : 500;
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
			$this->response->getHeaders()->add('Allow', implode(',', $exception->getAllowedMethods()));
		}

		$response->send();
	}
}
