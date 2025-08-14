<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response\senders;

use Closure;
use mako\http\Request;
use mako\http\Response;
use Override;

use function flush;
use function ob_end_clean;
use function ob_get_level;
use function printf;
use function strlen;

/**
 * Stream response.
 */
class Stream implements ResponseSenderInterface
{
	/**
	 * Is PHP running as a CGI program?
	 */
	protected bool $isCGI;

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
	public function flush(?string $chunk, bool $flushEmpty = false): void
	{
		if ($this->isCGI) {
			if (!empty($chunk)) {
				echo $chunk;

				flush();
			}
		}
		else {
			if (!empty($chunk) || $flushEmpty === true) {
				printf("%x\r\n%s\r\n", strlen($chunk ?? ''), $chunk ?? '');

				flush();
			}
		}
	}

	/**
	 * Sends the stream.
	 */
	protected function flow(): void
	{
		// Erase output buffers and disable output buffering

		while (ob_get_level() > 0) {
			ob_end_clean();
		}

		// Send the stream

		$stream = $this->stream;

		$stream($this);

		// Send empty chunk to tell the client that we're done

		$this->flush(null, true);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function send(Request $request, Response $response): void
	{
		$this->isCGI = $request->isCGI();

		if (!$this->isCGI) {
			$response->headers->add('Transfer-Encoding', 'chunked');
		}

		if (!empty($this->contentType)) {
			$response->setType($this->contentType);
		}

		if (!empty($this->charset)) {
			$response->setCharset($this->charset);
		}

		$response->sendHeaders();

		$this->flow();
	}
}
