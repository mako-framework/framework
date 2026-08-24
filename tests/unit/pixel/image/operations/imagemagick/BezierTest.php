<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations\imagemagick;

use InvalidArgumentException;
use mako\pixel\image\Color;
use mako\pixel\image\geometry\Point;
use mako\pixel\image\geometry\Points;
use mako\pixel\image\operations\imagemagick\Bezier;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('imagick')]
class BezierTest extends TestCase
{
	/**
	 *
	 */
	public function testInvalidPoints(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessageIs('A Bézier curve requires at least 2 points.');

		new Bezier(
			new Points(
				new Point(0, 0),
			),
			new Color(0, 0, 0)
		);
	}

	/**
	 *
	 */
	public function testInvalidStrokeWidth(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessageIs('Stroke width must be greater than 0.');

		new Bezier(
			new Points(
				new Point(0, 0),
				new Point(0, 0),
			),
			stroke: new Color(0, 0, 0),
			strokeWidth: 0
		);
	}
}
