<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations\gd;

use InvalidArgumentException;
use mako\pixel\image\Color;
use mako\pixel\image\operations\gd\Border;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('gd')]
class BorderTest extends TestCase
{
	/**
	 *
	 */
	public function testNegativeWidth(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessageIs('The border width must be a non-negative number.');

		new Border(new Color(0, 0, 0), -4);
	}
}
