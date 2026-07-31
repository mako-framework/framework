<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations\gd;

use mako\pixel\image\Gd;
use mako\pixel\image\operations\AspectRatio;
use mako\pixel\image\operations\gd\Resize;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class ResizeTest extends TestCase
{
	/**
	 *
	 */
	public function setUp(): void
	{
		if (!extension_loaded('gd')) {
			$this->markTestSkipped('The "gd" extension is not enabled.');
		}
	}

	/**
	 *
	 */
	public function testResize(): void
	{
		$image = new Gd(__DIR__ . '/../../fixtures/001.png');

		$image->apply(new Resize(50, 50));

		$this->assertSame(50, $image->getHeight());
		$this->assertSame(50, $image->getWidth());
	}

	/**
	 *
	 */
	public function testResizeAspectRatioHeight(): void
	{
		$image = new Gd(__DIR__ . '/../../fixtures/001.png');

		$image->apply(new Resize(100, 50, AspectRatio::Height));

		$this->assertSame(50, $image->getHeight());
		$this->assertSame(50, $image->getWidth());
	}

	/**
	 *
	 */
	public function testResizeAspectRatioWidth(): void
	{
		$image = new Gd(__DIR__ . '/../../fixtures/001.png');

		$image->apply(new Resize(100, 50, AspectRatio::Width));

		$this->assertSame(100, $image->getHeight());
		$this->assertSame(100, $image->getWidth());
	}

	/**
	 *
	 */
	public function testResizeAspectRatioIgnore(): void
	{
		$image = new Gd(__DIR__ . '/../../fixtures/001.png');

		$image->apply(new Resize(100, 50, AspectRatio::Ignore));

		$this->assertSame(50, $image->getHeight());
		$this->assertSame(100, $image->getWidth());
	}
}
