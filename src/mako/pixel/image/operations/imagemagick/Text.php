<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use ImagickDraw;
use ImagickPixel;
use mako\pixel\image\geometry\Point;
use mako\pixel\image\operations\Font;
use mako\pixel\image\operations\OperationInterface;
use Override;

/**
 * Draws text on the image.
 */
class Text implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $text,
		protected Font $font,
		protected Point $position,
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
		$draw = new ImagickDraw;

		try {
			$draw->setFont($this->font->path);
			$draw->setFontSize($this->font->size);
			$draw->setFillColor(new ImagickPixel($this->font->color->toHexaString()));

			$imageResource->annotateImage(
				$draw,
				$this->position->x,
				$this->position->y,
				0,
				$this->text,
			);
		}
		finally {
			$draw->clear();
			$draw->destroy();
		}
	}
}
