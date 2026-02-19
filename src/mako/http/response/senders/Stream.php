<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response\senders;

use Closure;
use mako\http\Request;
use mako\http\Response;
use mako\http\response\senders\stream\StreamTrait;
use Override;

/**
 * Stream response.
 */
class Stream implements ResponseSenderInterface
{
	use StreamTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Closure $stream,
		protected ?string $contentType = null,
		protected ?string $charset = null
	) {
	}

	/**
	 * Sets the response content type.
	 *
	 * @return $this
	 */
	public function setType(string $contentType): Stream
	{
		$this->contentType = $contentType;

		return $this;
	}

	/**
	 * Returns the response content type.
	 */
	public function getType(): ?string
	{
		return $this->contentType;
	}

	/**
	 * Sets the response character set.
	 *
	 * @return $this
	 */
	public function setCharset(string $charset): Stream
	{
		$this->charset = $charset;

		return $this;
	}

	/**
	 * Returns the response character set.
	 */
	public function getCharset(): ?string
	{
		return $this->charset;
	}

	/**
	 * Flushes a chunck of data.
	 */
	public function flush(string $chunk): void
	{
		$this->sendChunk($chunk);
	}

	/**
	 * Sends the stream to the client.
	 */
	protected function sendStream(): void
	{
		($this->stream)($this);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function send(Request $request, Response $response): void
	{
		if (!empty($this->contentType)) {
			$response->setType($this->contentType);
		}

		if (!empty($this->charset)) {
			$response->setCharset($this->charset);
		}

		$response->headers->add('X-Accel-Buffering', 'no');

		// Erase output buffers and disable output buffering

		$this->eraseAndDisableOutputBuffers();

		// Send headers

		$response->sendHeaders();

		// Send the stream

		$this->sendStream();
	}
}
