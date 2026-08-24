<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\geometry\Point;
use mako\pixel\image\operations\Font;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\traits\GdTrait;
use Override;

use function imagettftext;

/**
 * Draws text on the image.
 */
class Text implements OperationInterface
{
	use GdTrait;

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
		imagettftext(
			$imageResource,
			$this->font->size,
			0,
			$this->position->x,
			$this->position->y,
			$this->allocateColor($imageResource, $this->font->color),
			$this->font->path,
			$this->text,
		);
	}
}
