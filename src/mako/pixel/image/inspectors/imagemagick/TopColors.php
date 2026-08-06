<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\inspectors\imagemagick;

use Imagick;
use mako\pixel\image\Color;
use mako\pixel\image\inspectors\InspectorInterface;
use Override;

use function array_slice;
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
		protected bool $ignoreTransparent = true,
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

		$groups = [];

		foreach ($histogram as $pixel) {
			$rgba = $pixel->getColor(2);

			if ($hasAlphaChannel && $this->ignoreTransparent && $rgba['a'] === 0) {
				continue;
			}

			// Keep the two most significant bits from each RGB channel.

			$key = ($rgba['r'] & 0xC0) | (($rgba['g'] & 0xC0) >> 2) | (($rgba['b'] & 0xC0) >> 4);

			$count = $pixel->getColorCount();

			$groups[$key] ??= [
				'count' => 0,
				'r' => 0,
				'g' => 0,
				'b' => 0,
				'a' => 0,
			];

			$group = &$groups[$key];

			$group['count'] += $count;
			$group['r'] += $rgba['r'] * $count;
			$group['g'] += $rgba['g'] * $count;
			$group['b'] += $rgba['b'] * $count;
			$group['a'] += $rgba['a'] * $count;

			unset($group);
		}

		usort($groups, fn (array $a, array $b): int => $b['count'] <=> $a['count']);

		$colors = [];

		foreach (array_slice($groups, 0, $this->limit) as $group) {
			$colors[] = new Color(
				(int) ($group['r'] / $group['count']),
				(int) ($group['g'] / $group['count']),
				(int) ($group['b'] / $group['count']),
				(int) ($group['a'] / $group['count']),
			);
		}

		return $colors;
	}
}
