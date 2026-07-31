<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations\gd;

use InvalidArgumentException;
use mako\pixel\image\Gd;
use mako\pixel\image\operations\gd\Scale;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class ScaleTest extends TestCase
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
	public function testScale50pct(): void
	{
		$image = new Gd(__DIR__ . '/../../fixtures/001.png');

		$image->apply(new Scale(50));

		$this->assertSame(150, $image->getHeight());
		$this->assertSame(150, $image->getWidth());
	}

	/**
	 *
	 */
	public function testScale200pct(): void
	{
		$image = new Gd(__DIR__ . '/../../fixtures/001.png');

		$image->apply(new Scale(200));

		$this->assertSame(600, $image->getHeight());
		$this->assertSame(600, $image->getWidth());
	}

	/**
	 *
	 */
	public function testScaleInvalidPercent(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessageIs('Scale percentage must be greater than zero.');

		new Scale(0);
	}
}
