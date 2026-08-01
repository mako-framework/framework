<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\inspectors\gd;

use GdImage;
use mako\pixel\image\Color;
use mako\pixel\image\inspectors\InspectorInterface;
use Override;

use function array_keys;
use function array_map;
use function array_slice;
use function arsort;
use function explode;
use function imagecolorat;
use function imagesx;
use function imagesy;
use function intval;
use function max;
use function min;
use function round;

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
	 * @param GdImage &$imageResource
	 */
	#[Override]
	public function inspect(object &$imageResource): mixed
	{
		$step = 5;

		$width = imagesx($imageResource);
		$height = imagesy($imageResource);

		$buckets = [];

		for ($y = 0; $y < $height; $y += $step) {
			for ($x = 0; $x < $width; $x += $step) {
				$rgb = imagecolorat($imageResource, $x, $y);

				$alpha = 1 - ((($rgb & 0x7F000000) >> 24) / 127);

				if ($this->ignoreTransparent && $alpha === 0) {
					continue;
				}

				$r = max(0, min(255, (int) round((($rgb >> 16) & 0xFF) / 16) * 16));
				$g = max(0, min(255, (int) round((($rgb >> 8) & 0xFF) / 16) * 16));
				$b = max(0, min(255, (int) round(($rgb & 0xFF) / 16) * 16));

				$key = "$r,$g,$b,$alpha";

				$buckets[$key] = ($buckets[$key] ?? 0) + 1;
			}
		}

		arsort($buckets);

		$colors = [];

		foreach (array_slice(array_keys($buckets), 0, $this->limit) as $rgba) {
			[$r, $g, $b, $a] = array_map(intval(...), explode(',', $rgba));

			$colors[] = new Color($r, $g, $b, $a * 255);
		}

		return $colors;
	}
}
