<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations\imagemagick;

use mako\pixel\image\Color;
use mako\pixel\image\ImageMagick;
use mako\pixel\image\inspectors\imagemagick\TopColors;
use mako\pixel\image\operations\imagemagick\ReplaceColor;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('imagick')]
class ReplaceColorTest extends TestCase
{
	/**
	 *
	 */
	public function testReplaceColors(): void
	{
		$image = new ImageMagick(__DIR__ . '/../../fixtures/001.png');

		$colors = $image->inspect(new TopColors);

		$this->assertCount(3, $colors);

		$this->assertSame('#0376BB', $colors[0]->toHexString());
		$this->assertSame('#B51700', $colors[1]->toHexString());
		$this->assertSame('#047101', $colors[2]->toHexString());

		$image->apply(new ReplaceColor(
			Color::fromHex('#0376BB'),
			new Color(0, 0, 0)
		));

		$colors = $image->inspect(new TopColors);

		$this->assertCount(3, $colors);

		$this->assertSame('#000000', $colors[0]->toHexString());
		$this->assertSame('#B51700', $colors[1]->toHexString());
		$this->assertSame('#047101', $colors[2]->toHexString());
	}

	/**
	 *
	 */
	public function testReplaceColorsWithMaxTolerance(): void
	{
		$image = new ImageMagick(__DIR__ . '/../../fixtures/001.png');

		$colors = $image->inspect(new TopColors);

		$this->assertCount(3, $colors);

		$this->assertSame('#0376BB', $colors[0]->toHexString());
		$this->assertSame('#B51700', $colors[1]->toHexString());
		$this->assertSame('#047101', $colors[2]->toHexString());

		$image->apply(new ReplaceColor(
			Color::fromHex('#0376BB'),
			new Color(0, 0, 0),
			tolerance: 100
		));

		$colors = $image->inspect(new TopColors);

		$this->assertCount(1, $colors);

		$this->assertSame('#000000', $colors[0]->toHexString());
	}

	/**
	 *
	 */
	public function testReplaceColorsWithInvertedMatch(): void
	{
		$image = new ImageMagick(__DIR__ . '/../../fixtures/001.png');

		$colors = $image->inspect(new TopColors);

		$this->assertCount(3, $colors);

		$this->assertSame('#0376BB', $colors[0]->toHexString());
		$this->assertSame('#B51700', $colors[1]->toHexString());
		$this->assertSame('#047101', $colors[2]->toHexString());

		$image->apply(new ReplaceColor(
			Color::fromHex('#0376BB'),
			new Color(0, 0, 0),
			invertMatch: true
		));

		$colors = $image->inspect(new TopColors);

		$this->assertCount(2, $colors);

		$this->assertSame('#0376BB', $colors[0]->toHexString());
		$this->assertSame('#000000', $colors[1]->toHexString());
	}
}
