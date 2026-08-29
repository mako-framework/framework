<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use mako\pixel\image\operations\SrgbPipeline as BaseSrgbPipeline;
use Override;

/**
 * {@inheritDoc}
 */
class SrgbPipeline extends BaseSrgbPipeline
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$originalColorspace = $imageResource->getImageColorspace();

		$needsConversion = $originalColorspace !== Imagick::COLORSPACE_SRGB;

		if ($needsConversion) {
			$imageResource->transformImageColorspace(Imagick::COLORSPACE_SRGB);
		}

		try {
			parent::apply($imageResource);
		}
		finally {
			if ($needsConversion) {
				$imageResource->transformImageColorspace($originalColorspace);
			}
		}
	}
}
