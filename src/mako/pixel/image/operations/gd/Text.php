<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\operations\Text as TextOperation;
use mako\pixel\image\traits\GdTrait;
use Override;

use function imagettftext;

/**
 * {@inheritDoc}
 */
class Text extends TextOperation
{
	use GdTrait;

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
			$this->normalizeFontSize($this->font->size),
			0,
			$this->position->x,
			$this->position->y,
			$this->allocateColor($imageResource, $this->font->color),
			$this->font->path,
			$this->text,
		);
	}
}
