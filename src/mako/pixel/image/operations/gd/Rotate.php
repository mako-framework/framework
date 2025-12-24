<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use mako\pixel\image\operations\OperationInterface;
use Override;

use function imagecolorallocatealpha;
use function imagecolortransparent;
use function imagerotate;

/**
 * Rotates the image.
 */
class Rotate implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected int $degrees = 0
	) {
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param \GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if ($this->degrees === 0) {
			return;
		}

		$transparent = imagecolorallocatealpha($imageResource, 0, 0, 0, 127);

		$imageResource = imagerotate($imageResource, (360 - $this->degrees), imagecolorallocatealpha($imageResource, 0, 0, 0, 127));

		imagecolortransparent($imageResource, $transparent);
	}
}
