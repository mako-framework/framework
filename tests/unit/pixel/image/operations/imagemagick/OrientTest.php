<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations\imagemagick;

use mako\pixel\image\ColorFamily;
use mako\pixel\image\ImageMagick;
use mako\pixel\image\inspectors\imagemagick\ColorAt;
use mako\pixel\image\operations\imagemagick\Orient;
use mako\pixel\image\operations\Point;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('imagick')]
class OrientTest extends TestCase
{
	/**
	 *
	 */
	public function testOrient(): void
	{
		$files = glob(__DIR__ . '/../../fixtures/orientation/test_*.jpg');

		natsort($files);

		$expectedBeforeOrient = [
			[
				'tl' => ColorFamily::Red,
				'tr' => ColorFamily::Green,
				'bl' => ColorFamily::Blue,
				'br' => ColorFamily::White,
			],
			[
				'tl' => ColorFamily::Green,
				'tr' => ColorFamily::Red,
				'bl' => ColorFamily::White,
				'br' => ColorFamily::Blue,
			],
			[
				'tl' => ColorFamily::White,
				'tr' => ColorFamily::Blue,
				'bl' => ColorFamily::Green,
				'br' => ColorFamily::Red,
			],
			[
				'tl' => ColorFamily::Blue,
				'tr' => ColorFamily::White,
				'bl' => ColorFamily::Red,
				'br' => ColorFamily::Green,
			],
			[
				'tl' => ColorFamily::Red,
				'tr' => ColorFamily::Blue,
				'bl' => ColorFamily::Green,
				'br' => ColorFamily::White,
			],
			[
				'tl' => ColorFamily::Green,
				'tr' => ColorFamily::White,
				'bl' => ColorFamily::Red,
				'br' => ColorFamily::Blue,
			],
			[
				'tl' => ColorFamily::White,
				'tr' => ColorFamily::Green,
				'bl' => ColorFamily::Blue,
				'br' => ColorFamily::Red,
			],
			[
				'tl' => ColorFamily::Blue,
				'tr' => ColorFamily::Red,
				'bl' => ColorFamily::White,
				'br' => ColorFamily::Green,
			],
		];

		foreach ($files as $key => $file) {
			$image = new ImageMagick($file);

			$colorTl = $image->inspect(new ColorAt(new Point(2, 2)));
			$colorTr = $image->inspect(new ColorAt(new Point(48, 2)));
			$colorBl = $image->inspect(new ColorAt(new Point(2, 48)));
			$colorBr = $image->inspect(new ColorAt(new Point(48, 48)));

			$this->assertSame($expectedBeforeOrient[$key]['tl'], $colorTl->toColorFamily(), $file);
			$this->assertSame($expectedBeforeOrient[$key]['tr'], $colorTr->toColorFamily(), $file);
			$this->assertSame($expectedBeforeOrient[$key]['bl'], $colorBl->toColorFamily(), $file);
			$this->assertSame($expectedBeforeOrient[$key]['br'], $colorBr->toColorFamily(), $file);

			$image->apply(new Orient);

			$colorTl = $image->inspect(new ColorAt(new Point(2, 2)));
			$colorTr = $image->inspect(new ColorAt(new Point(48, 2)));
			$colorBl = $image->inspect(new ColorAt(new Point(2, 48)));
			$colorBr = $image->inspect(new ColorAt(new Point(48, 48)));

			$this->assertSame(ColorFamily::Red, $colorTl->toColorFamily(), $file);
			$this->assertSame(ColorFamily::Green, $colorTr->toColorFamily(), $file);
			$this->assertSame(ColorFamily::Blue, $colorBl->toColorFamily(), $file);
			$this->assertSame(ColorFamily::White, $colorBr->toColorFamily(), $file);
		}
	}
}
