<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\operations\Font;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\Point;
use Override;

use function imagecolorallocatealpha;
use function imagettftext;
use function round;

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
	 * @param GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$color = imagecolorallocatealpha(
			$imageResource,
			$this->font->color->red,
			$this->font->color->green,
			$this->font->color->blue,
			127 - (int) round($this->font->color->alpha / 255 * 127),
		);

		imagettftext(
			$imageResource,
			$this->font->size,
			0,
			$this->position->x,
			$this->position->y,
			$color,
			$this->font->path,
			$this->text,
		);
	}
}
