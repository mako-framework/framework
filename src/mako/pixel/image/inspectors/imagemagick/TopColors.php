<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\inspectors\imagemagick;

use Imagick;
use mako\pixel\image\Color;
use mako\pixel\image\inspectors\TopColors as TopColorsInspector;
use Override;

use function array_slice;
use function usort;

/**
 * {@inheritDoc}
 */
class TopColors extends TopColorsInspector
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function inspect(object $imageResource): mixed
	{
		$needsConversion = $imageResource->getImageColorspace() !== Imagick::COLORSPACE_SRGB;

		$image = $needsConversion ? clone $imageResource : $imageResource;

		if ($needsConversion) {
			$image->transformImageColorspace(Imagick::COLORSPACE_SRGB);
		}

		$hasAlphaChannel = $image->getImageAlphaChannel();

		$histogram = $image->getImageHistogram();

		if ($needsConversion) {
			$image->clear();
			$image->destroy();
		}

		$groups = [];

		foreach ($histogram as $pixel) {
			$rgba = $pixel->getColor(2);

			if ($hasAlphaChannel && $this->ignoreTransparent && $rgba['a'] === 0) {
				continue;
			}

			// Group similar colors by quantizing each RGB channel to its two most significant bits.
			// Track the total pixel count for the group so that groups can be ranked by overall count
			// and keep only the most frequently occurring individual color.

			$key = ($rgba['r'] & 0xC0) | (($rgba['g'] & 0xC0) >> 2) | (($rgba['b'] & 0xC0) >> 4);

			$groups[$key] ??= [
				'totalCount' => 0,
				'dominantCount' => 0,
				'r' => 0,
				'g' => 0,
				'b' => 0,
				'a' => 0,
			];

			$count = $pixel->getColorCount();

			$groups[$key]['totalCount'] += $count;

			if ($count > $groups[$key]['dominantCount']) {
				$groups[$key]['dominantCount'] = $count;
				$groups[$key]['r'] = $rgba['r'];
				$groups[$key]['g'] = $rgba['g'];
				$groups[$key]['b'] = $rgba['b'];
				$groups[$key]['a'] = $rgba['a'];
			}
		}

		usort($groups, fn (array $a, array $b): int => $b['totalCount'] <=> $a['totalCount']);

		$colors = [];

		foreach (array_slice($groups, 0, $this->limit) as $group) {
			$colors[] = new Color(
				$group['r'],
				$group['g'],
				$group['b'],
				$group['a'],
			);
		}

		return $colors;
	}
}
