<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\inspectors\gd;

use mako\pixel\image\Gd;
use mako\pixel\image\inspectors\gd\TopColors;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('gd')]
class TopColorsTest extends TestCase
{
	/**
	 * Note that the colors extracted by GD aren't 100% accurate.
	 */
	public function testTopColors(): void
	{
		$image = new Gd(__DIR__ . '/../../fixtures/001.png');

		$colors = $image->inspect(new TopColors);

		$this->assertCount(3, $colors);

		$this->assertSame('#0070C0', $colors[0]->toHexString());
		$this->assertSame('#B01000', $colors[1]->toHexString());
		$this->assertSame('#007000', $colors[2]->toHexString());
	}

	/**
	 * Note that the colors extracted by GD aren't 100% accurate.
	 */
	public function testTopColorsWithLimit(): void
	{
		$image = new Gd(__DIR__ . '/../../fixtures/001.png');

		$colors = $image->inspect(new TopColors(2));

		$this->assertCount(2, $colors);

		$this->assertSame('#0070C0', $colors[0]->toHexString());
		$this->assertSame('#B01000', $colors[1]->toHexString());
	}
}
