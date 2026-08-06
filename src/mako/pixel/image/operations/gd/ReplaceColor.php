<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\Color;
use mako\pixel\image\inspectors\gd\traits\InspectorTrait;
use mako\pixel\image\operations\gd\traits\OperationTrait;
use mako\pixel\image\operations\OperationInterface;
use Override;

use function abs;
use function imagecolorat;
use function imageistruecolor;
use function imagepalettetotruecolor;
use function imagesetpixel;
use function imagesx;
use function imagesy;
use function max;
use function min;

/**
 * Replaces pixels matching a specified color with another color.
 */
class ReplaceColor implements OperationInterface
{
	use InspectorTrait;
	use OperationTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Color $from,
		protected Color $to,
		protected int $tolerance = 0,
		protected bool $invertMatch = false
	) {
	}

	/**
	 * Converts tolerance percentage (0-100) to 8-bit channel range (0-255).
	 */
	protected function normalizeTolerance(): float
	{
		$tolerance = max(0, min(100, $this->tolerance));

		return ($tolerance / 100) * 255.0;
	}

	/**
	 * Returns TRUE if pixel color matches target color within tolerance and FALSE if not.
	 */
	protected function matchesColor(array $pixelColor, array $targetColor, float $tolerance): bool
	{
		$distance = max(
			abs($pixelColor[0] - $targetColor[0]),
			abs($pixelColor[1] - $targetColor[1]),
			abs($pixelColor[2] - $targetColor[2]),
			abs($pixelColor[3] - $targetColor[3])
		);

		return $distance <= $tolerance;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if (!imageistruecolor($imageResource)) {
			imagepalettetotruecolor($imageResource);
		}

		$width = imagesx($imageResource);
		$height = imagesy($imageResource);

		$fromRgba = [
			$this->from->red,
			$this->from->green,
			$this->from->blue,
			$this->from->alpha,
		];

		$tolerance = $this->normalizeTolerance();

		$replacementColor = $this->allocateColor($imageResource, $this->to);

		for ($y = 0; $y < $height; ++$y) {
			for ($x = 0; $x < $width; ++$x) {
				$matches = $this->matchesColor(
					$this->convertColorToRgba(imagecolorat($imageResource, $x, $y)),
					$fromRgba,
					$tolerance
				);

				if ($this->invertMatch ? !$matches : $matches) {
					imagesetpixel($imageResource, $x, $y, $replacementColor);
				}
			}
		}
	}
}
