<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\web;

use Throwable;

use mako\http\exceptions\MethodNotAllowedException;
use mako\http\exceptions\RequestException;

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
		if($exception instanceof RequestException)
		{
			$status = (int) $exception->getCode();

			if($exception instanceof MethodNotAllowedException)
			{
				$this->response->header('Allow', implode(',', $exception->getAllowedMethods()));
			}
		}

		return $status ?? 500;
	}
}
