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
use mako\pixel\image\operations\imagemagick\Pixel;
use mako\pixel\image\operations\imagemagick\Rotate;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('imagick')]
class RotateTest extends TestCase
{
	/**
	 * Creates a 2x2 image with the following pixels:
	 *
	 * R G
	 * B W
	 */
	protected function createImage(): ImageMagick
	{
		$image = ImageMagick::create(new Dimensions(2, 2), new Color(255, 0, 0));

		$image->apply(new Pixel(new Point(1, 0), new Color(0, 255, 0)));
		$image->apply(new Pixel(new Point(0, 1), new Color(0, 0, 255)));
		$image->apply(new Pixel(new Point(1, 1), new Color(255, 255, 255)));

		return $image;
	}

	/**
	 * Returns [degrees, topLeft, topRight, bottomLeft, bottomRight].
	 *
	 * The driver rotates clockwise (imagerotate is called with 360 - degrees).
	 */
	public static function rotationProvider(): array
	{
		return [
			// R G      B R
			// B W  ->  W G
			'90'  => [90, '#0000FF', '#FF0000', '#FFFFFF', '#00FF00'],

			// R G      W B
			// B W  ->  G R
			'180' => [180, '#FFFFFF', '#0000FF', '#00FF00', '#FF0000'],

			// R G      G W
			// B W  ->  R B
			'270' => [270, '#00FF00', '#FFFFFF', '#FF0000', '#0000FF'],

			// Full rotation should leave the image unchanged
			'360' => [360, '#FF0000', '#00FF00', '#0000FF', '#FFFFFF'],
		];
	}

	/**
	 *
	 */
	#[DataProvider('rotationProvider')]
	public function testRotate(int $degrees, string $topLeft, string $topRight, string $bottomLeft, string $bottomRight): void
	{
		$image = $this->createImage();

		$image->apply(new Rotate($degrees));

		$this->assertSame($topLeft, $image->inspect(new ColorAt(new Point(0, 0)))->toHexString());
		$this->assertSame($topRight, $image->inspect(new ColorAt(new Point(1, 0)))->toHexString());
		$this->assertSame($bottomLeft, $image->inspect(new ColorAt(new Point(0, 1)))->toHexString());
		$this->assertSame($bottomRight, $image->inspect(new ColorAt(new Point(1, 1)))->toHexString());
	}
}
