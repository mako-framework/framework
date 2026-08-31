<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations\imagemagick;

use mako\pixel\image\Color;
use mako\pixel\image\geometry\Dimensions;
use mako\pixel\image\geometry\Point;
use mako\pixel\image\ImageMagick;
use mako\pixel\image\inspectors\imagemagick\ColorAt;
use mako\pixel\image\operations\imagemagick\Invert;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('imagick')]
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
		$image = ImageMagick::create(new Dimensions(1, 1), $color);

		$image->apply(new Invert);

		$this->assertSame($expectedColor, $image->inspect(new ColorAt(new Point(0, 0)))->toHexString());
	}

	/**
	 *
	 */
	public function testDoubleInvertRestoresOriginalColor(): void
	{
		$image = ImageMagick::create(new Dimensions(1, 1), new Color(100, 150, 200));

		$image->apply(new Invert);
		$image->apply(new Invert);

		$this->assertSame('#6496C8', $image->inspect(new ColorAt(new Point(0, 0)))->toHexString());
	}
}
