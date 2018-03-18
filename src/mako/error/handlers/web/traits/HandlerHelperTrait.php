<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\web\traits;

use mako\http\exceptions\MethodNotAllowedException;
use mako\http\exceptions\RequestException;
use mako\http\Response;
use Throwable;

/**
 * Handler helper trait.
 *
 * @author Frederic G. Østby
 */
trait HandlerHelperTrait
{
	/**
	 * Checks if we meet the requirements to return as a specific type.
	 *
	 * @param  string $function Required function
	 * @param  array  $mimes    Mimetypes
	 * @param  string $partial  Partial mimetype
	 * @return bool
	 */
	protected function returnAs(string $function, array $mimes, string $partial)
	{
		if(function_exists($function))
		{
			$responseType = $this->response->getType();

			if(in_array($responseType, $mimes) || strpos($responseType, $partial) !== false)
			{
				return true;
			}

			$accepts = $this->request->getHeaders()->acceptableContentTypes();

			if(isset($accepts[0]) && (in_array($accepts[0], $mimes) || strpos($accepts[0], $partial) !== false))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Should we return the error as JSON?
	 *
	 * @return bool
	 */
	protected function returnAsJson(): bool
	{
		return $this->returnAs('json_encode', ['application/json', 'text/json'], '+json');
	}

	/**
	 * Should we return the error as XML?
	 *
	 * @return bool
	 */
	protected function returnAsXml(): bool
	{
		return $this->returnAs('simplexml_load_string', ['application/xml', 'text/xml'], '+xml');
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
			$this->response->getHeaders()->add('Allow', implode(',', $exception->getAllowedMethods()));
		}

		$response->send();
	}
}
