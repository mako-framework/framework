<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\traits;

use function in_array;
use function strpos;

/**
 * Respond with trait.
 *
 * @author Frederic G. Østby
 */
trait RespondWithTrait
{
	/**
	 * Should we respond with the provided mime type?
	 *
	 * @param  array       $mimeTypes Mime types
	 * @param  string|null $suffix    Mime type suffix
	 * @return bool
	 */
	protected function respondWith(array $mimeTypes, ?string $suffix = null): bool
	{
		$responseType = $this->response->getType();

		if(in_array($responseType, $mimeTypes) || ($suffix !== null && strpos($responseType, $suffix) !== false))
		{
			return true;
		}

		$accepts = $this->request->getHeaders()->getAcceptableContentTypes();

		if(isset($accepts[0]) && (in_array($accepts[0], $mimeTypes) || ($suffix !== null && strpos($accepts[0], $suffix) !== false)))
		{
			return true;
		}

		return false;
	}

	/**
	 * Should we respond with JSON?
	 *
	 * @return bool
	 */
	protected function respondWithJson(): bool
	{
		return $this->respondWith(['application/json', 'text/json'], '+json');
	}

	/**
	 * Should we respond with XML?
	 *
	 * @return bool
	 */
	protected function respondWithXml(): bool
	{
		return $this->respondWith(['application/xml', 'text/xml'], '+xml');
	}
}
