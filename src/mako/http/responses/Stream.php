<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\http\responses;

use Closure;
use mako\http\Request;
use mako\http\Response;
use mako\http\responses\ResponseContainerInterface;

/**
 * Stream response.
 *
 * @author  Frederic G. Østby
 */

class Stream implements ResponseContainerInterface
{
	/**
	 * Is PHP running as a CGI program?
	 *
	 * @var boolean
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
	 * @access  public
	 * @param   \Closure  $stream  Stream
	 */

	public function __construct(Closure $stream)
	{
		$this->stream = $stream;
	}

	/**
	 * Flushes a chunck of data.
	 *
	 * @access  public
	 * @param   string   $chunk       Chunck of data to flush
	 * @param   boolean  $flushEmpty  Flush empty chunk?
	 */

	public function flush($chunk, $flushEmpty = false)
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
	 *
	 * @access  protected
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
			$response->header('content-encoding', 'chunked');
			$response->header('transfer-encoding', 'chunked');
		}

		$response->sendHeaders();

		$this->flow();
	}
}