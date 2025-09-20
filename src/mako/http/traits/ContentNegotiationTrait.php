<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\traits;

use function in_array;
use function str_contains;

/**
 * Basic content negotiation.
 *
 * @property \mako\http\Request  $request
 * @property \mako\http\Response $response
 */
trait ContentNegotiationTrait
{
	/**
	 * Does the client expect the provided mime type?
	 */
	protected function expectsType(array $mimeTypes, ?string $suffix = null): bool
	{
		$accepts = $this->request->headers->getAcceptableContentTypes();

		if (isset($accepts[0]) && (in_array($accepts[0], $mimeTypes) || ($suffix !== null && str_contains($accepts[0], $suffix)))) {
			return true;
		}

		return false;
	}

	/**
	 * Does the client expect JSON?
	 */
	protected function expectsJson(): bool
	{
		return $this->expectsType(['application/json', 'text/json'], '+json');
	}

	/**
	 * Does the client expect XML?
	 */
	protected function expectsXml(): bool
	{
		return $this->expectsType(['application/xml', 'text/xml'], '+xml');
	}

	/**
	 * Should we respond with the provided mime type?
	 */
	protected function respondWithType(array $mimeTypes, ?string $suffix = null): bool
	{
		$responseType = $this->response->getType();

		if (in_array($responseType, $mimeTypes) || ($suffix !== null && str_contains($responseType, $suffix))) {
			return true;
		}

		return $this->expectsType($mimeTypes, $suffix);
	}

	/**
	 * Should we respond with JSON?
	 */
	protected function respondWithJson(): bool
	{
		return $this->respondWithType(['application/json', 'text/json'], '+json');
	}

	/**
	 * Should we respond with XML?
	 */
	protected function respondWithXml(): bool
	{
		return $this->respondWithType(['application/xml', 'text/xml'], '+xml');
	}
}
