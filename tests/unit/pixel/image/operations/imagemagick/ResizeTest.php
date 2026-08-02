<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations\imagemagick;

use mako\pixel\image\Dimensions;
use mako\pixel\image\ImageMagick;
use mako\pixel\image\operations\AspectRatio;
use mako\pixel\image\operations\imagemagick\Resize;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('imagick')]
class ResizeTest extends TestCase
{
	/**
	 *
	 */
	public function testResize(): void
	{
		$image = new ImageMagick(__DIR__ . '/../../fixtures/001.png');

		$image->apply(new Resize(new Dimensions(50, 50)));

		$this->assertSame(50, $image->getHeight());
		$this->assertSame(50, $image->getWidth());
	}

	/**
	 *
	 */
	public function testResizeAspectRatioHeight(): void
	{
		$image = new ImageMagick(__DIR__ . '/../../fixtures/001.png');

		$image->apply(new Resize(new Dimensions(100, 50), AspectRatio::Height));

		$this->assertSame(50, $image->getHeight());
		$this->assertSame(50, $image->getWidth());
	}

	/**
	 *
	 */
	public function testResizeAspectRatioWidth(): void
	{
		$image = new ImageMagick(__DIR__ . '/../../fixtures/001.png');

		$image->apply(new Resize(new Dimensions(100, 50), AspectRatio::Width));

		$this->assertSame(100, $image->getHeight());
		$this->assertSame(100, $image->getWidth());
	}

	/**
	 *
	 */
	public function testResizeAspectRatioIgnore(): void
	{
		$image = new ImageMagick(__DIR__ . '/../../fixtures/001.png');

		$image->apply(new Resize(new Dimensions(100, 50), AspectRatio::Ignore));

		$this->assertSame(50, $image->getHeight());
		$this->assertSame(100, $image->getWidth());
	}
}
