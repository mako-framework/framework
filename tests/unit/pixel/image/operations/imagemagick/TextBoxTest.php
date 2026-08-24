<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations\imagemagick;

use InvalidArgumentException;
use mako\pixel\image\Color;
use mako\pixel\image\geometry\Dimensions;
use mako\pixel\image\operations\Font;
use mako\pixel\image\operations\imagemagick\TextBox;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('imagick')]
class TextBoxTest extends TestCase
{
	/**
	 *
	 */
	public function testInvalidStrokeWidth(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessageIs('Stroke width must be greater than 0.');

		new TextBox(
			'Hello, world!',
			new Dimensions(0, 0),
			new Font('foo', 1),
			stroke: new Color(0, 0, 0),
			strokeWidth: 0
		);
	}
}
