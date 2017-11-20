<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\web\traits;

use Throwable;

use mako\http\Response;
use mako\http\exceptions\RequestException;
use mako\http\exceptions\MethodNotAllowedException;

/**
 * Handler helper trait.
 *
 * @author Frederic G. Østby
 */
trait HandlerHelperTrait
{
	/**
	 * Should we return the error as JSON?
	 *
	 * @return bool
	 */
	protected function returnAsJson(): bool
	{
		if(function_exists('json_encode'))
		{
			$jsonMimeTypes = ['application/json', 'text/json'];

			if($this->request->isAjax() || in_array($this->response->getType(), $jsonMimeTypes))
			{
				return true;
			}

			$acceptableContentTypes = $this->request->acceptableContentTypes();

			if(isset($acceptableContentTypes[0]) && in_array($acceptableContentTypes[0], $jsonMimeTypes))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Should we return the error as XML?
	 *
	 * @return bool
	 */
	protected function returnAsXml(): bool
	{
		if(function_exists('simplexml_load_string'))
		{
			$xmlMimeTypes = ['application/xml', 'text/xml'];

			if(in_array($this->response->getType(), $xmlMimeTypes))
			{
				return true;
			}

			$acceptableContentTypes = $this->request->acceptableContentTypes();

			if(isset($acceptableContentTypes[0]) && in_array($acceptableContentTypes[0], $xmlMimeTypes))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns the status code that we should send.
	 *
	 * @param  \Throwable $exception Exception
	 * @return int
	 */
	protected function getStatusCode(Throwable $exception): int
	{
		return ($exception instanceof RequestException) ? $exception->getCode() : 500;
	}

	/**
	 * Sends response and adds any aditional headers.
	 *
	 * @param \mako\http\Response $response  Response
	 * @param \Throwable          $exception Exception
	 */
	protected function sendResponse(Response $response, Throwable $exception)
	{
		if($exception instanceof MethodNotAllowedException)
		{
			$this->response->header('Allow', implode(',', $exception->getAllowedMethods()));
		}

		$response->send();
	}
}
