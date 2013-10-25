<?php

namespace mako\http;

use \Closure;

/**
 * Stream container.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

Class StreamContainer
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Stream.
	 * 
	 * @var \Closure
	 */

	protected $stream;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

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

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Flushes a chunck of data.
	 * 
	 * @access  public
	 * @param   string   $chunk       Chunck of data to flush
	 * @param   boolean  $flushEmpty  (optional) Flush empty chunk?
	 */

	public function flush($chunk, $flushEmpty = false)
	{
		if(!empty($chunk) || $flushEmpty === true)
		{
			printf("%x\r\n%s\r\n", strlen($chunk), $chunk);

			flush();
		}
	}

	/**
	 * Sends the stream.
	 * 
	 * @access  public
	 */

	public function flow()
	{
		// Erase output buffers and disable output buffering

		while(ob_get_level() > 0) ob_end_clean();

		// Send the stream

		$stream = $this->stream;

		$stream($this);

		// Send empty chunk to tell the client that we're done

		$this->flush(null, true);
	}
}

/** -------------------- End of file -------------------- **/