<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use InvalidArgumentException;
use mako\pixel\image\Color;
use mako\pixel\image\Dimensions;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\Point;
use mako\pixel\image\traits\GdTrait;
use Override;

use function cos;
use function deg2rad;
use function floor;
use function imagealphablending;
use function imagearc;
use function imagefilledpolygon;
use function imageline;
use function imagesetthickness;
use function min;
use function sin;

/**
 * Draws a rounded rectangle on the image.
 */
class RoundedRectangle implements OperationInterface
{
	use GdTrait;

	/**
	 * Number of segments used to approximate each corner arc.
	 */
	protected const int CORNER_SEGMENTS = 12;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Dimensions $dimensions,
		protected int $radius,
		protected ?Color $fill = null,
		protected ?Color $stroke = null,
		protected int $strokeWidth = 1,
		protected Point $position = new Point(0, 0)
	) {
		if ($this->fill === null && $this->stroke === null) {
			throw new InvalidArgumentException('A rounded rectangle requires a fill, a stroke, or both.');
		}

		if ($this->stroke !== null && $this->strokeWidth < 1) {
			throw new InvalidArgumentException('Stroke width must be greater than 0.');
		}

		// Clamp the radius so that it never exceeds half the width or height,
		// preventing corner arcs from overlapping and producing a malformed polygon.

		$this->radius = min(
			$this->radius,
			(int) floor($this->dimensions->width / 2),
			(int) floor($this->dimensions->height / 2),
		);
	}

	/**
	 * Builds a point list approximating the rounded rectangle outline.
	 */
	protected function buildOutline(int $x1, int $y1, int $x2, int $y2): array
	{
		$radius = $this->radius;

		$corners = [
			['cx' => $x2 - $radius, 'cy' => $y1 + $radius, 'start' => 270, 'end' => 360], // Top right.
			['cx' => $x2 - $radius, 'cy' => $y2 - $radius, 'start' => 0,   'end' => 90],  // Bottom right.
			['cx' => $x1 + $radius, 'cy' => $y2 - $radius, 'start' => 90,  'end' => 180], // Bottom left.
			['cx' => $x1 + $radius, 'cy' => $y1 + $radius, 'start' => 180, 'end' => 270], // Top left.
		];

		$points = [];

		foreach ($corners as $corner) {
			for ($i = 0; $i <= static::CORNER_SEGMENTS; $i++) {
				$angle = deg2rad($corner['start'] + ($corner['end'] - $corner['start']) * ($i / static::CORNER_SEGMENTS));

				$points[] = (int) ($corner['cx'] + $radius * cos($angle));
				$points[] = (int) ($corner['cy'] + $radius * sin($angle));
			}
		}

		return $points;
	}

	/**
	 * Draws the fill as a single polygon to avoid overlapping/blending artifacts.
	 */
	protected function drawFill(GdImage $imageResource, int $x1, int $y1, int $x2, int $y2, int $color): void
	{
		$points = $this->buildOutline($x1, $y1, $x2, $y2);

		imagefilledpolygon($imageResource, $points, $color);
	}

	/**
	 * Draws the stroke using arcs for the corners and lines for the straight edges.
	 */
	protected function drawStroke(GdImage $imageResource, int $x1, int $y1, int $x2, int $y2, int $color): void
	{
		$radius = $this->radius;
		$diameter = $radius * 2;

		imagesetthickness($imageResource, $this->strokeWidth);

		// Straight edges.

		imageline($imageResource, $x1 + $radius, $y1, $x2 - $radius, $y1, $color);
		imageline($imageResource, $x1 + $radius, $y2, $x2 - $radius, $y2, $color);
		imageline($imageResource, $x1, $y1 + $radius, $x1, $y2 - $radius, $color);
		imageline($imageResource, $x2, $y1 + $radius, $x2, $y2 - $radius, $color);

		// Corners.

		imagearc($imageResource, $x1 + $radius, $y1 + $radius, $diameter, $diameter, 180, 270, $color);
		imagearc($imageResource, $x2 - $radius, $y1 + $radius, $diameter, $diameter, 270, 360, $color);
		imagearc($imageResource, $x2 - $radius, $y2 - $radius, $diameter, $diameter, 0, 90, $color);
		imagearc($imageResource, $x1 + $radius, $y2 - $radius, $diameter, $diameter, 90, 180, $color);

		imagesetthickness($imageResource, 1);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		imagealphablending($imageResource, true);

		$x1 = $this->position->x;
		$y1 = $this->position->y;
		$x2 = $this->position->x + $this->dimensions->width;
		$y2 = $this->position->y + $this->dimensions->height;

		if ($this->fill !== null) {
			$this->drawFill($imageResource, $x1, $y1, $x2, $y2, $this->allocateColor($imageResource, $this->fill));
		}

		if ($this->stroke !== null) {
			$this->drawStroke($imageResource, $x1, $y1, $x2, $y2, $this->allocateColor($imageResource, $this->stroke));
		}
	}
}
