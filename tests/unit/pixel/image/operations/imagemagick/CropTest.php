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
use mako\pixel\image\operations\imagemagick\Crop;
use mako\pixel\image\operations\imagemagick\Pixel;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('imagick')]
class CropTest extends TestCase
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
	 * Returns [crop position, expected color of the remaining pixel].
	 */
	public static function cropProvider(): array
	{
		return [
			'top left'     => [new Point(0, 0), '#FF0000'],
			'top right'    => [new Point(1, 0), '#00FF00'],
			'bottom left'  => [new Point(0, 1), '#0000FF'],
			'bottom right' => [new Point(1, 1), '#FFFFFF'],
		];
	}

	/**
	 *
	 */
	#[DataProvider('cropProvider')]
	public function testCrop(Point $position, string $expectedColor): void
	{
		$image = $this->createImage();

		$image->apply(new Crop(new Dimensions(1, 1), $position));

		$this->assertSame(1, $image->getWidth());
		$this->assertSame(1, $image->getHeight());

		$this->assertSame($expectedColor, $image->inspect(new ColorAt(new Point(0, 0)))->toHexString());
	}

	/**
	 *
	 */
	public function testCropRegion(): void
	{
		$image = $this->createImage();

		// Crop the right 1x2 column

		$image->apply(new Crop(new Dimensions(1, 2), new Point(1, 0)));

		$this->assertSame(1, $image->getWidth());
		$this->assertSame(2, $image->getHeight());

		$this->assertSame('#00FF00', $image->inspect(new ColorAt(new Point(0, 0)))->toHexString());
		$this->assertSame('#FFFFFF', $image->inspect(new ColorAt(new Point(0, 1)))->toHexString());
	}
}
