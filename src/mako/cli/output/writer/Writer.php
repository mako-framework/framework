<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\writer;

use function fstat;
use function fwrite;

/**
 * Writer.
 */
abstract class Writer implements WriterInterface
{
	/**
	 * Output stream.
	 *
	 * @var resource
	 */
	protected $stream;

	/**
	 * Is the stream direct?
	 *
	 * @var bool
	 */
	protected $isDirect;

	/**
	 * {@inheritDoc}
	 */
	public function setStream($stream): void
	{
		$this->stream = $stream;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isDirect(): bool
	{
		if($this->isDirect === null)
		{
			$this->isDirect = (0020000 === (fstat($this->stream)['mode'] & 0170000));
		}

		return $this->isDirect;
	}

	/**
	 * {@inheritDoc}
	 */
	public function write(string $string): void
	{
		fwrite($this->stream, $string);
	}
}
