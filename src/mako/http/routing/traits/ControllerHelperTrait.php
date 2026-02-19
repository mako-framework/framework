<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing\traits;

use Closure;
use Deprecated;
use mako\http\response\builders\JSON;
use mako\http\response\senders\File;
use mako\http\response\senders\Stream;
use mako\syringe\traits\ContainerAwareTrait;

/**
 * Controller helper trait.
 */
trait ControllerHelperTrait
{
	use ContainerAwareTrait;
	use RedirectTrait {
		redirect as redirectResponse;
	}

	/**
	 * Returns a file response container.
	 */
	#[Deprecated('create and return a "mako\http\response\senders\File" instance instead', since: 'Mako 12.1.0')]
	protected function fileResponse(string $file): File
	{
		return new File($this->fileSystem, $file);
	}

	/**
	 * Returns a stream response container.
	 */
	#[Deprecated('create and return a "mako\http\response\senders\Stream" instance instead', since: 'Mako 12.1.0')]
	protected function streamResponse(Closure $stream, ?string $contentType = null, ?string $charset = null): Stream
	{
		return new Stream($stream, $contentType, $charset ?? $this->response->getCharset());
	}

	/**
	 * Returns a JSON response builder.
	 */
	#[Deprecated('create and return a "mako\http\response\builders\JSON" instance instead', since: 'Mako 12.1.0')]
	protected function jsonResponse(mixed $data, int $options = 0, ?int $status = null, ?string $charset = null): JSON
	{
		return new JSON($data, $options, $status ?? $this->response->getStatus(), $charset ?? $this->response->getCharset());
	}
}
