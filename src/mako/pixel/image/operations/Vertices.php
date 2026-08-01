<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use Countable;
use IteratorAggregate;
use mako\pixel\image\Dimensions;
use Override;
use Traversable;

use function array_map;
use function count;
use function max;
use function min;
use function round;

/**
 * Vertices.
 *
 * @implements IteratorAggregate<int, Point>
 */
class Vertices implements Countable, IteratorAggregate
{
	/**
	 *  Vertices.
	 *
	 * @var array<Point>
	 */
	protected readonly array $points;

	/**
	 * Dimensions.
	 */
	protected ?Dimensions $dimensions = null;

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
	 * Returns the vertices.
	 *
	 * @return array<Point>
	 */
	public function getPoints(): array
	{
		return $this->points;
	}

	/**
	 * Returns the dimensions of the bounding box containing the vertices.
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
	 * Returns a new set of vertices fitted to the given dimensions and normalized to 0,0.
	 */
	public function fitTo(Dimensions $dimensions): self
	{
		$current = $this->getDimensions();

		$scale = min(
			$dimensions->width / $current->width,
			$dimensions->height / $current->height,
		);

		$points = [];

		foreach ($this->points as $point) {
			$points[] = new Point(
				(int) round($point->x * $scale),
				(int) round($point->y * $scale),
			);
		}

		$minX = min(...array_map(fn (Point $p) => $p->x, $points));
		$minY = min(...array_map(fn (Point $p) => $p->y, $points));

		foreach ($points as $key => $point) {
			$points[$key] = new Point(
				$point->x - $minX,
				$point->y - $minY,
			);
		}

		return new self(...$points);
	}
}
