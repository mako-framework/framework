<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\inspectors\gd;

use GdImage;
use mako\pixel\image\Color;
use mako\pixel\image\inspectors\gd\traits\InspectorTrait;
use mako\pixel\image\inspectors\InspectorInterface;
use Override;

use function array_keys;
use function array_slice;
use function arsort;
use function imagecolorat;
use function imagesx;
use function imagesy;

/**
 * Top colors inspector.
 *
 * @implements InspectorInterface<array<int, Color>>
 */
class TopColors implements InspectorInterface
{
	use InspectorTrait;

	/**
	 * Pixel sampling interval used when scanning images.
	 */
	protected const int SAMPLE_STEP = 5;

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
	 * @param GdImage &$imageResource
	 */
	#[Override]
	public function inspect(object &$imageResource): mixed
	{
		$width = imagesx($imageResource);
		$height = imagesy($imageResource);

		$colorBuckets = [];

		for ($y = 0; $y < $height; $y += static::SAMPLE_STEP) {
			for ($x = 0; $x < $width; $x += static::SAMPLE_STEP) {
				$color = imagecolorat($imageResource, $x, $y);

				if ($this->ignoreTransparent && (($color & 0x7F000000) >> 24) === 127) {
					continue;
				}

				$colorBuckets[$color] = ($colorBuckets[$color] ?? 0) + 1;
			}
		}

		arsort($colorBuckets);

		$colors = [];

		foreach (array_slice(array_keys($colorBuckets), 0, $this->limit) as $color) {
			[$r, $g, $b, $a] = $this->convertColorToRgba($color);

			$colors[] = new Color($r, $g, $b, $a);
		}

		return $colors;
	}
}
