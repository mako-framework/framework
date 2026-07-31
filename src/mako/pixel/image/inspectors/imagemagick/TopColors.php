<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\inspectors\imagemagick;

use Imagick;
use ImagickPixel;
use mako\pixel\image\Color;
use mako\pixel\image\inspectors\InspectorInterface;
use Override;

use function count;
use function usort;

/**
 * Top colors inspector.
 *
 * @implements InspectorInterface<array<int, Color>>
 */
class TopColors implements InspectorInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected int $limit = 5,
		protected bool $ignoreTransparent = true
	) {
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function inspect(object &$imageResource): mixed
	{
		$image = clone $imageResource;

		$image->setImageColorspace(Imagick::COLORSPACE_RGB);

		$image->quantizeImage(64, Imagick::COLORSPACE_RGB, 0, false, false);

		$histogram = $image->getImageHistogram();

		usort($histogram, fn (ImagickPixel $a, ImagickPixel $b): int => $b->getColorCount() <=> $a->getColorCount());

		$colors = [];

		foreach ($histogram as $pixel) {
			if (count($colors) >= $this->limit) {
				break;
			}

			$rgba = $pixel->getColor(2); // 2 = RGBA normalized to 0-255

			if ($this->ignoreTransparent && $rgba['a'] === 0) {
				continue;
			}

			$colors[] = new Color($rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
		}

		$image->clear();
		$image->destroy();

		return $colors;
	}
}
