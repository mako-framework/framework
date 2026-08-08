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

		foreach ($files as $file) {
			$image = new ImageMagick($file);

			$image->apply(new Orient);

			$colorTl = $image->inspect(new ColorAt(new Point(2, 2)));
			$colorTr = $image->inspect(new ColorAt(new Point(48, 2)));
			$colorBl = $image->inspect(new ColorAt(new Point(2, 98)));
			$colorBr = $image->inspect(new ColorAt(new Point(48, 98)));

			$this->assertSame(ColorFamily::Red, $colorTl->toColorFamily(), $file);
			$this->assertSame(ColorFamily::Green, $colorTr->toColorFamily(), $file);
			$this->assertSame(ColorFamily::Blue, $colorBl->toColorFamily(), $file);
			$this->assertSame(ColorFamily::White, $colorBr->toColorFamily(), $file);
		}
	}
}
