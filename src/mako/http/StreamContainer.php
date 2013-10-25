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
	 * Flushes the buffer.
	 * 
	 * @access  public
	 */

	public function flush()
	{
		flush();
	}

	/**
	 * Sends the stream.
	 * 
	 * @access  public
	 */

	public function flow()
	{
		while(ob_get_level() > 0) ob_end_clean(); // Erase and close open output buffers

		$stream = $this->stream;

		$stream($this);
	}
}

/** -------------------- End of file -------------------- **/