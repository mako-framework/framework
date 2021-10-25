<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\writer;

use function fstat;
use function fwrite;

/**
 * Standard writer.
 *
 * @author Frederic G. Østby
 */
class Standard implements WriterInterface
{
	/**
	 * Is the stream direct?
	 *
	 * @var bool
	 */
	protected $isDirect;

	/**
	 * {@inheritDoc}
	 */
	public function isDirect(): bool
	{
		if($this->isDirect === null)
		{
			$this->isDirect = (0020000 === (fstat(STDOUT)['mode'] & 0170000));
		}

		return $this->isDirect;
	}

	/**
	 * {@inheritDoc}
	 */
	public function write(string $string): void
	{
		fwrite(STDOUT, $string);
	}
}
