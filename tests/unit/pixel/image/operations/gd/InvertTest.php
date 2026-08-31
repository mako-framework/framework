<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations\gd;

use mako\pixel\image\Color;
use mako\pixel\image\Gd;
use mako\pixel\image\geometry\Dimensions;
use mako\pixel\image\geometry\Point;
use mako\pixel\image\inspectors\gd\ColorAt;
use mako\pixel\image\operations\gd\Invert;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('gd')]
class InvertTest extends TestCase
{
	/**
	 * Returns [original color, expected inverted color].
	 */
	public static function colorProvider(): array
	{
		return [
			'white'    => [new Color(255, 255, 255), '#000000'],
			'black'    => [new Color(0, 0, 0), '#FFFFFF'],
			'red'      => [new Color(255, 0, 0), '#00FFFF'],
			'green'    => [new Color(0, 255, 0), '#FF00FF'],
			'blue'     => [new Color(0, 0, 255), '#FFFF00'],
			'mid gray' => [new Color(100, 150, 200), '#9B6937'],
		];
	}

	/**
	 *
	 */
	#[DataProvider('colorProvider')]
	public function testInvert(Color $color, string $expectedColor): void
	{
		$image = Gd::create(new Dimensions(1, 1), $color);

		$image->apply(new Invert);

		$this->assertSame($expectedColor, $image->inspect(new ColorAt(new Point(0, 0)))->toHexString());
	}

	/**
	 *
	 */
	public function testDoubleInvertRestoresOriginalColor(): void
	{
		$image = Gd::create(new Dimensions(1, 1), new Color(100, 150, 200));

		$image->apply(new Invert);
		$image->apply(new Invert);

		$this->assertSame('#6496C8', $image->inspect(new ColorAt(new Point(0, 0)))->toHexString());
	}
}
