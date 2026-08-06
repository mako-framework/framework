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
 * Extracts the dominant colors from an image.
 *
 * Similar colors are grouped together to avoid returning multiple
 * variations of the same color.
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

		// Ensure truecolor image for accurate colors

		$cloneCreated = false;

		$image = $this->createTruecolorCopyIfNeeded($imageResource, $width, $height, $cloneCreated);

		// Extract colors

		$colorBuckets = [];
		$representatives = [];

		for ($y = 0; $y < $height; $y += static::SAMPLE_STEP) {
			for ($x = 0; $x < $width; $x += static::SAMPLE_STEP) {
				$color = imagecolorat($image, $x, $y);

				if ($this->ignoreTransparent && (($color & 0x7F000000) >> 24) === 127) {
					continue;
				}

				// Group similar colors by quantizing each RGB channel to its two most significant bits.

				$bucket = $color & 0x00C0C0C0;

				$colorBuckets[$bucket] = ($colorBuckets[$bucket] ?? 0) + 1;
				$representatives[$bucket] ??= $color;
			}
		}

		// Destroy clone if one was created

		if ($cloneCreated) {
			$image = null;
		}

		// Return top colors

		arsort($colorBuckets);

		$colors = [];

		foreach (array_slice(array_keys($colorBuckets), 0, $this->limit) as $bucket) {
			[$r, $g, $b, $a] = $this->convertColorToRgba($representatives[$bucket]);

			$colors[] = new Color($r, $g, $b, $a);
		}

		return $colors;
	}
}
