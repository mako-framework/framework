<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\writer;

use Override;

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
	 */
	protected ?bool $isDirect = null;

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function setStream($stream): void
	{
		$this->stream = $stream;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function isDirect(): bool
	{
		if ($this->isDirect === null) {
			$this->isDirect = (0o020000 === (fstat($this->stream)['mode'] & 0o170000));
		}

		return $this->isDirect;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function write(string $string): void
	{
		fwrite($this->stream, $string);
	}
}
