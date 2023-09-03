<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing\traits;

use Closure;
use mako\http\response\builders\JSON;
use mako\http\response\senders\File;
use mako\http\response\senders\Redirect;
use mako\http\response\senders\Stream;
use mako\syringe\traits\ContainerAwareTrait;

/**
 * Controller helper trait.
 */
trait ControllerHelperTrait
{
	use ContainerAwareTrait;

	/**
	 * Returns a file response container.
	 */
	protected function fileResponse(string $file): File
	{
		return new File($this->fileSystem, $file);
	}

	/**
	 * Returns a redirect response container.
	 */
	protected function redirectResponse(string $location, array $routeParams = [], array $queryParams = [], string $separator = '&', bool|string $language = true): Redirect
	{
		if($this->routes->hasNamedRoute($location))
		{
			$location = $this->urlBuilder->toRoute($location, $routeParams, $queryParams, $separator, $language);
		}

		return new Redirect($location);
	}

	/**
	 * Returns a stream response container.
	 */
	protected function streamResponse(Closure $stream, ?string $contentType = null, ?string $charset = null): Stream
	{
		return new Stream($stream, $contentType, $charset ?? $this->response->getCharset());
	}

	/**
	 * Returns a JSON response builder.
	 */
	protected function jsonResponse(mixed $data, int $options = 0, ?int $status = null, ?string $charset = null): JSON
	{
		return new JSON($data, $options, $status ?? $this->response->getStatus(), $charset ?? $this->response->getCharset());
	}
}
