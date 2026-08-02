<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations\imagemagick;

use InvalidArgumentException;
use mako\pixel\image\ImageMagick;
use mako\pixel\image\operations\imagemagick\Scale;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('imagick')]
class ScaleTest extends TestCase
{
	/**
	 *
	 */
	public function testScale50pct(): void
	{
		$image = new ImageMagick(__DIR__ . '/../../fixtures/001.png');

		$image->apply(new Scale(50));

		$this->assertSame(150, $image->getHeight());
		$this->assertSame(150, $image->getWidth());
	}

	/**
	 *
	 */
	public function testScale200pct(): void
	{
		$image = new ImageMagick(__DIR__ . '/../../fixtures/001.png');

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
