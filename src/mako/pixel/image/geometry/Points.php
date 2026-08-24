<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\geometry;

use Countable;
use IteratorAggregate;
use Override;
use Traversable;

use function count;
use function max;
use function min;
use function round;

/**
 * Points.
 *
 * @implements IteratorAggregate<int, Point>
 */
final class Points implements Countable, IteratorAggregate
{
	/**
	 *  Points.
	 *
	 * @var array<Point>
	 */
	private readonly array $points;

	/**
	 * Dimensions.
	 */
	private ?Dimensions $dimensions = null;

	/**
	 * Constructor.
	 */
	public function __construct(Point ...$point)
	{
		$this->points = $point;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function count(): int
	{
		return count($this->points);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getIterator(): Traversable
	{
		yield from $this->points;
	}

	/**
	 * Returns the points contained in the collection.
	 *
	 * @return array<Point>
	 */
	public function getPoints(): array
	{
		return $this->points;
	}

	/**
	 * Returns the dimensions of the bounding box containing the points.
	 */
	public function getDimensions(): Dimensions
	{
		if ($this->dimensions === null) {
			$minX = PHP_INT_MAX;
			$maxX = PHP_INT_MIN;
			$minY = PHP_INT_MAX;
			$maxY = PHP_INT_MIN;

			foreach ($this->points as $point) {
				$minX = min($minX, $point->x);
				$maxX = max($maxX, $point->x);
				$minY = min($minY, $point->y);
				$maxY = max($maxY, $point->y);
			}

			$this->dimensions = new Dimensions(
				$maxX - $minX,
				$maxY - $minY,
			);
		}

		return $this->dimensions;
	}

	/**
	 * Returns a new set of points fitted to the given dimensions
	 * while preserving the aspect ratio and normalized to 0,0.
	 */
	public function fitTo(Dimensions $dimensions): self
	{
		$current = $this->getDimensions();

		$scale = min(
			$dimensions->width / $current->width,
			$dimensions->height / $current->height,
		);

		$coordinates = [];

		$minX = PHP_INT_MAX;
		$minY = PHP_INT_MAX;

		foreach ($this->points as $point) {
			$x = (int) round($point->x * $scale);
			$y = (int) round($point->y * $scale);

			$coordinates[] = [$x, $y];

			$minX = min($minX, $x);
			$minY = min($minY, $y);
		}

		$points = [];

		foreach ($coordinates as [$x, $y]) {
			$points[] = new Point(
				$x - $minX,
				$y - $minY,
			);
		}

		return new self(...$points);
	}
}
