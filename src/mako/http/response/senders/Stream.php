<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response\senders;

use Closure;
use mako\http\Request;
use mako\http\Response;

use function flush;
use function ob_end_clean;
use function ob_get_level;
use function printf;
use function strlen;

/**
 * Stream response.
 *
 * @author Frederic G. Østby
 */
class Stream implements ResponseSenderInterface
{
	/**
	 * Is PHP running as a CGI program?
	 *
	 * @var bool
	 */
	protected $isCGI;

	/**
	 * Stream.
	 *
	 * @var \Closure
	 */
	protected $stream;

	/**
	 * Content type.
	 *
	 * @var string|null
	 */
	protected $contentType;

	/**
	 * Character set.
	 *
	 * @var string|null
	 */
	protected $charset;

	/**
	 * Constructor.
	 *
	 * @param \Closure    $stream      Stream
	 * @param string|null $contentType Content type
	 * @param string|null $charset     Character set
	 */
	public function __construct(Closure $stream, ?string $contentType = null, ?string $charset = null)
	{
		$this->stream = $stream;

		$this->contentType = $contentType;

		$this->charset = $charset;
	}

	/**
	 * Sets the response content type.
	 *
	 * @param  string                             $contentType Content type
	 * @return \mako\http\response\senders\Stream
	 */
	public function setType(string $contentType): Stream
	{
		$this->contentType = $contentType;

		return $this;
	}

	/**
	 * Sets the response character set.
	 *
	 * @param  string                             $charset Character set
	 * @return \mako\http\response\senders\Stream
	 */
	public function setCharset(string $charset): Stream
	{
		$this->charset = $charset;

		return $this;
	}

	/**
	 * Returns the response character set.
	 *
	 * @return string|null
	 */
	public function getCharset(): ?string
	{
		return $this->charset;
	}

	/**
	 * Flushes a chunck of data.
	 *
	 * @param string|null $chunk      Chunck of data to flush
	 * @param bool        $flushEmpty Flush empty chunk?
	 */
	public function flush(?string $chunk, bool $flushEmpty = false): void
	{
		if($this->isCGI)
		{
			if(!empty($chunk))
			{
				echo $chunk;

				flush();
			}
		}
		else
		{
			if(!empty($chunk) || $flushEmpty === true)
			{
				printf("%x\r\n%s\r\n", strlen($chunk), $chunk);

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

		while(ob_get_level() > 0) ob_end_clean();

		// Send the stream

		$stream = $this->stream;

		$stream($this);

		// Send empty chunk to tell the client that we're done

		$this->flush(null, true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function send(Request $request, Response $response): void
	{
		$this->isCGI = $request->isCGI();

		if(!$this->isCGI)
		{
			$response->getHeaders()->add('Content-Encoding', 'chunked');
			$response->getHeaders()->add('Transfer-Encoding', 'chunked');
		}

		if(!empty($this->contentType))
		{
			$response->setType($this->contentType);
		}

		if(!empty($this->charset))
		{
			$response->setCharset($this->charset);
		}

		$response->sendHeaders();

		$this->flow();
	}
}
