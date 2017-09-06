<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response\senders;

use Closure;
use mako\http\Request;
use mako\http\Response;
use mako\http\response\senders\ResponseSenderInterface;

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
	 * Constructor.
	 *
	 * @param \Closure $stream Stream
	 */
	public function __construct(Closure $stream)
	{
		$this->stream = $stream;
	}

	/**
	 * Flushes a chunck of data.
	 *
	 * @param string|null $chunk      Chunck of data to flush
	 * @param bool        $flushEmpty Flush empty chunk?
	 */
	public function flush(string $chunk = null, bool $flushEmpty = false)
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
	protected function flow()
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
	public function send(Request $request, Response $response)
	{
		$this->isCGI = $request->isCGI();

		if(!$this->isCGI)
		{
			$response->header('Content-Encoding', 'chunked');
			$response->header('Transfer-Encoding', 'chunked');
		}

		$response->sendHeaders();

		$this->flow();
	}
}
