<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations\gd;

use InvalidArgumentException;
use mako\pixel\image\Color;
use mako\pixel\image\geometry\Point;
use mako\pixel\image\geometry\Points;
use mako\pixel\image\operations\gd\Polyline;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('gd')]
class PolylineTest extends TestCase
{
	/**
	 *
	 */
	public function testInvalidPoints(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessageIs('A polyline requires at least 2 points.');

		new Polyline(
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

		new Polyline(
			new Points(
				new Point(0, 0),
				new Point(0, 0),
			),
			stroke: new Color(0, 0, 0),
			strokeWidth: 0
		);
	}
}
