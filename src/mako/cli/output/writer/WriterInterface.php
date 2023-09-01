<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\writer;

/**
 * Writer interface.
 */
interface WriterInterface
{
	/**
	 * Sets the output stream.
	 *
	 * @param resource $stream
	 */
	public function setStream($stream): void;

	/**
	 * Returns TRUE if the output isn't redirected or piped and FALSE in all other situations.
	 */
	public function isDirect(): bool;

	/**
	 * Writes output.
	 */
	public function write(string $string): void;
}
