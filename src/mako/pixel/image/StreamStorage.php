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
	case Temp = 'php://temp';
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
