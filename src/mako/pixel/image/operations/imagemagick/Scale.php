<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use InvalidArgumentException;
use mako\pixel\image\operations\OperationInterface;
use Override;

use function round;

/**
 * Scales the image.
 */
class Scale implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected int $percent {
			set(int $value) {
				if ($value <= 0) {
					throw new InvalidArgumentException('Scale percentage must be greater than zero.');
				}
				$this->percent = $value;
			}
		},
	) {
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$oldWidth = $imageResource->getImageWidth();
		$oldHeight = $imageResource->getImageHeight();

		$newWidth = (int) round($oldWidth * ($this->percent / 100));
		$newHeight = (int) round($oldHeight * ($this->percent / 100));

		$imageResource->scaleImage($newWidth, $newHeight);
	}
}
