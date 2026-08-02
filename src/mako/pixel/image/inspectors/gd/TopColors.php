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
use function array_map;
use function array_slice;
use function arsort;
use function explode;
use function imagecolorat;
use function imagesx;
use function imagesy;
use function intval;

/**
 * Top colors inspector.
 *
 * @implements InspectorInterface<array<int, Color>>
 */
class TopColors implements InspectorInterface
{
	use InspectorTrait;

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
				$color = imagecolorat($imageResource, $x, $y);

				if ($this->ignoreTransparent && (($color & 0x7F000000) >> 24) === 127) {
					continue;
				}

				[$r, $g, $b, $a] = $this->convertColorToRgba($color);

				$key = "$r,$g,$b,$a";

				$buckets[$key] = ($buckets[$key] ?? 0) + 1;
			}
		}

		arsort($buckets);

		$colors = [];

		foreach (array_slice(array_keys($buckets), 0, $this->limit) as $rgba) {
			[$r, $g, $b, $a] = array_map(intval(...), explode(',', $rgba));

			$colors[] = new Color($r, $g, $b, $a);
		}

		return $colors;
	}
}
