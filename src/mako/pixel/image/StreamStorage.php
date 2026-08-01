<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image;

use mako\pixel\image\exceptions\ImageException;

use function fopen;
use function sprintf;

/**
 * Stream storage enum.
 */
enum StreamStorage: string
{
	/**
	 * Stores stream data in temporary storage.
	 *
	 * Data is kept in memory until the configured memory limit is reached,
	 * after which it is stored in a temporary file.
	 */
	case Temp = 'php://temp';

	/**
	 * Stores stream data entirely in memory.
	 */
	case Memory = 'php://memory';

	/**
	 * Creates a stream.
	 *
	 * @return resource
	 */
	public function create(): mixed
	{
		$stream = fopen($this->value, 'w+b');

		if ($stream === false) {
			throw new ImageException(sprintf('Unable to open stream [ %s ].', $this->value));
		}

		return $stream;
	}
}
