<?php

namespace mako\tests\unit\pixel\image;

use mako\pixel\image\Gd;
use mako\tests\TestCase;

// red: B51700
// blue: 0376BB
// green: 047101

class GdTest extends TestCase
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
	public function testDimensions(): void
	{
		$image = new Gd(__DIR__ . '/fixtures/001.png');

		$this->assertSame(300, $image->getWidth());

		$this->assertSame(300, $image->getHeight());

		$this->assertSame(['width' => 300, 'height' => 300], $image->getDimensions());
	}
}
