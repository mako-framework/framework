<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\inspectors\gd;

use GdImage;
use mako\pixel\image\Color;
use mako\pixel\image\inspectors\TopColors as BaseTopColors;
use mako\pixel\image\traits\GdTrait;
use Override;

use function array_slice;
use function ceil;
use function imagecolorat;
use function imagesx;
use function imagesy;
use function log;
use function max;
use function min;
use function usort;

/**
 * {@inheritDoc}
 */
class TopColors extends BaseTopColors
{
	use GdTrait;

	/**
	 * Upper bound on the sampling step, so that very large images don't get
	 * sampled too sparsely and risk missing small but visually significant colors.
	 */
	protected const int MAX_SAMPLE_STEP = 8;

	/**
	 * Calculates the sampling step size based on image dimensions.
	 *
	 * The step grows logarithmically with the image's megapixel count, so that
	 * sampling density decreases gradually as images get larger, rather than
	 * dropping sharply at certain resolution thresholds.
	 */
	protected function getSampleStep(int $width, int $height): int
	{
		$megapixels = ($width * $height) / 1_000_000;

		$step = (int) ceil(1 + log(max(1, $megapixels), 2));

		return max(1, min(static::MAX_SAMPLE_STEP, $step));
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

		$sampleStep = $this->getSampleStep($width, $height);

		// Ensure truecolor image for accurate colors

		$image = $this->createTruecolorCopyIfNeeded($imageResource, $width, $height, $copyCreated);

		// Extract colors

		$colorCounts = [];
		$groups = [];

		for ($y = 0; $y < $height; $y += $sampleStep) {
			for ($x = 0; $x < $width; $x += $sampleStep) {
				$color = imagecolorat($image, $x, $y);

				if ($this->ignoreTransparent && (($color & 0x7F000000) >> 24) === 127) {
					continue;
				}

				// Group similar colors by quantizing each RGB channel to its two most significant bits.
				// Track the total sample count for the group so that groups can be ranked by
				// overall frequency and keep only the most frequently occurring individual color.

				$key = $color & 0x00C0C0C0;

				$colorCounts[$key][$color] ??= 0;

				$groups[$key] ??= [
					'totalCount' => 0,
					'dominantCount' => 0,
					'color' => $color,
				];

				$colorCount = ++$colorCounts[$key][$color];

				$groups[$key]['totalCount']++;

				if ($colorCount > $groups[$key]['dominantCount']) {
					$groups[$key]['dominantCount'] = $colorCount;
					$groups[$key]['color'] = $color;
				}
			}
		}

		// Destroy copy if one was created

		if ($copyCreated) {
			$image = null;
		}

		// Return top colors

		usort($groups, fn (array $a, array $b): int => $b['totalCount'] <=> $a['totalCount']);

		$colors = [];

		foreach (array_slice($groups, 0, $this->limit) as $group) {
			[$r, $g, $b, $a] = $this->convertColorToRgba($group['color']);

			$colors[] = new Color($r, $g, $b, $a);
		}

		return $colors;
	}
}
