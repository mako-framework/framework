<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations\imagemagick;

use InvalidArgumentException;
use mako\pixel\image\operations\imagemagick\Pixelate;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('imagick')]
class PixelateTest extends TestCase
{
	/**
	 *
	 */
	public function testPixelSizeMustBeGreaterThanOne(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessageIs('Pixel size must be greater than 1.');

		new Pixelate(1);
	}
}
