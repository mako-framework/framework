<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\traits;

use function in_array;
use function strpos;

/**
 * Basic content negotiation.
 *
 * @author Frederic G. Østby
 *
 * @property \mako\http\Request  $request
 * @property \mako\http\Response $response
 */
trait ContentNegotiationTrait
{
	/**
	 * Does the client expect the provided mime type?
	 *
	 * @param  array       $mimeTypes Mime types
	 * @param  string|null $suffix    Mime type suffix
	 * @return bool
	 */
	protected function expectsType(array $mimeTypes, ?string $suffix = null): bool
	{
		$accepts = $this->request->getHeaders()->getAcceptableContentTypes();

		if(isset($accepts[0]) && (in_array($accepts[0], $mimeTypes) || ($suffix !== null && strpos($accepts[0], $suffix) !== false)))
		{
			return true;
		}

		return false;
	}

	/**
	 * Does the client expect JSON?
	 *
	 * @return bool
	 */
	protected function expectsJson(): bool
	{
		return $this->expectsType(['application/json', 'text/json'], '+json');
	}

	/**
	 * Does the client expect XML?
	 *
	 * @return bool
	 */
	protected function expectsXml(): bool
	{
		return $this->expectsType(['application/xml', 'text/xml'], '+xml');
	}

	/**
	 * Should we respond with the provided mime type?
	 *
	 * @param  array       $mimeTypes Mime types
	 * @param  string|null $suffix    Mime type suffix
	 * @return bool
	 */
	protected function respondWithType(array $mimeTypes, ?string $suffix = null): bool
	{
		$responseType = $this->response->getType();

		if(in_array($responseType, $mimeTypes) || ($suffix !== null && strpos($responseType, $suffix) !== false))
		{
			return true;
		}

		return $this->expectsType($mimeTypes, $suffix);
	}

	/**
	 * Should we respond with JSON?
	 *
	 * @return bool
	 */
	protected function respondWithJson(): bool
	{
		return $this->respondWithType(['application/json', 'text/json'], '+json');
	}

	/**
	 * Should we respond with XML?
	 *
	 * @return bool
	 */
	protected function respondWithXml(): bool
	{
		return $this->respondWithType(['application/xml', 'text/xml'], '+xml');
	}
}
