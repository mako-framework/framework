<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response\senders\stream;

use function flush;
use function ob_end_clean;
use function ob_get_level;

/**
 * Stream trait.
 */
trait StreamTrait
{
	/**
	 * Erases and disables output buffers.
	 */
	protected function eraseAndDisableOutputBuffers(): void
	{
		while (ob_get_level() > 0) {
			ob_end_clean();
		}
	}

	/**
	 * Sends chunk to the client.
	 */
	protected function sendChunk(string $chunk): void
	{
		echo $chunk;

		flush();
	}
}
