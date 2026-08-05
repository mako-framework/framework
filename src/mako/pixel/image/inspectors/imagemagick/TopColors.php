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
		$hasAlphaChannel = $imageResource->getImageAlphaChannel();

		$histogram = $imageResource->getImageHistogram();

		usort($histogram, fn (ImagickPixel $a, ImagickPixel $b): int => $b->getColorCount() <=> $a->getColorCount());

		$colors = [];

		foreach ($histogram as $pixel) {
			if (count($colors) >= $this->limit) {
				break;
			}

			$color = $pixel->getColor(1);

			$r = (int) round($color['r'] * 255);
			$g = (int) round($color['g'] * 255);
			$b = (int) round($color['b'] * 255);
			$a = (int) round($pixel->getColorValue(Imagick::COLOR_ALPHA) * 255);

			if ($hasAlphaChannel && $this->ignoreTransparent && $a === 0) {
				continue;
			}

			$colors[] = new Color($r, $g, $b, $a);
		}

		return $colors;
	}
}
