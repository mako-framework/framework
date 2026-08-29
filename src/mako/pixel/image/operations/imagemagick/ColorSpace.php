<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use mako\pixel\image\exceptions\ImageException;
use mako\pixel\image\operations\ColorSpace as BaseColorSpace;
use Override;

use function constant;
use function defined;
use function sprintf;
use function strtoupper;

/**
 * {@inheritDoc}
 */
class ColorSpace extends BaseColorSpace
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$constant = Imagick::class . '::COLORSPACE_' . strtoupper($this->colorSpace->value);

		if (!defined($constant)) {
			throw new ImageException(sprintf(
				'The [ %s ] color space is not supported by the installed version of Imagick.',
				$this->colorSpace->value
			));
		}

		$imageResource->transformImageColorspace(constant($constant));
	}
}
