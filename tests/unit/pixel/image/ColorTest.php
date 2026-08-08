<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image;

use InvalidArgumentException;
use mako\pixel\image\Color;
use mako\pixel\image\ColorFamily;
use mako\pixel\image\WebColor;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class ColorTest extends TestCase
{
	/**
	 *
	 */
	public function testConstructorWithValidArguments(): void
	{
		$color = new Color(50, 100, 150, 200);

		$this->assertSame(50, $color->red);
		$this->assertSame(100, $color->green);
		$this->assertSame(150, $color->blue);
		$this->assertSame(200, $color->alpha);

		$this->assertSame(50, $color->getRed());
		$this->assertSame(100, $color->getGreen());
		$this->assertSame(150, $color->getBlue());
		$this->assertSame(200, $color->getAlpha());
	}

	/**
	 *
	 */
	public function testConstructorWithInvaliRedArgument(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$this->expectExceptionMessageIs('Red must be between 0 and 255.');

		$color = new Color(300, 255, 255);
	}

	/**
	 *
	 */
	public function testConstructorWithInvaliGreenArgument(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$this->expectExceptionMessageIs('Green must be between 0 and 255.');

		$color = new Color(255, 300, 255);
	}

	/**
	 *
	 */
	public function testConstructorWithInvaliBlueArgument(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$this->expectExceptionMessageIs('Blue must be between 0 and 255.');

		$color = new Color(255, 255, 300);
	}

	/**
	 *
	 */
	public function testConstructorWithInvaliAlphaArgument(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$this->expectExceptionMessageIs('Alpha must be between 0 and 255.');

		$color = new Color(255, 255, 255, 300);
	}

	/**
	 *
	 */
	public function testFromHexWithValidArgument(): void
	{
		$color = Color::fromHex('FF0000');

		$this->assertSame(255, $color->getRed());
		$this->assertSame(0, $color->getGreen());
		$this->assertSame(0, $color->getBlue());
		$this->assertSame(255, $color->getAlpha());

		//

		$color = Color::fromHex('#FF0000');

		$this->assertSame(255, $color->getRed());
		$this->assertSame(0, $color->getGreen());
		$this->assertSame(0, $color->getBlue());
		$this->assertSame(255, $color->getAlpha());

		//

		$color = Color::fromHex('#00FF00');

		$this->assertSame(0, $color->getRed());
		$this->assertSame(255, $color->getGreen());
		$this->assertSame(0, $color->getBlue());
		$this->assertSame(255, $color->getAlpha());

		//

		$color = Color::fromHex('#0000FF');

		$this->assertSame(0, $color->getRed());
		$this->assertSame(0, $color->getGreen());
		$this->assertSame(255, $color->getBlue());
		$this->assertSame(255, $color->getAlpha());

		//

		$color = Color::fromHex('#FF00007F');

		$this->assertSame(255, $color->getRed());
		$this->assertSame(0, $color->getGreen());
		$this->assertSame(0, $color->getBlue());
		$this->assertSame(127, $color->getAlpha());
	}

	/**
	 *
	 */
	public function testFromHexWithInvalidArgument(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$this->expectExceptionMessageIs('Invalid hex color format.');

		$color = Color::fromHex('foobar');
	}

	/**
	 *
	 */
	public function testFromWebColor(): void
	{
		$color = Color::fromWebColor(WebColor::SeaGreen);

		$this->assertSame(WebColor::SeaGreen->value, $color->toHexString());
	}

	/**
	 *
	 */
	public function testWith(): void
	{
		$color1 = new Color(0, 0, 0);

		$this->assertSame(0, $color1->red);
		$this->assertSame(0, $color1->green);
		$this->assertSame(0, $color1->blue);
		$this->assertSame(255, $color1->alpha);

		$color2 = $color1->with(red: 100);

		$this->assertNotSame($color1, $color2);

		$this->assertSame(100, $color2->red);
		$this->assertSame(0, $color2->green);
		$this->assertSame(0, $color2->blue);
		$this->assertSame(255, $color2->alpha);

		$color3 = $color2->with(green: 110, blue: 120, alpha: 127);

		$this->assertNotSame($color2, $color3);

		$this->assertSame(100, $color3->red);
		$this->assertSame(110, $color3->green);
		$this->assertSame(120, $color3->blue);
		$this->assertSame(127, $color3->alpha);
	}

	/**
	 *
	 */
	public function testInvert(): void
	{
		$color = new Color(0, 0, 0);

		$inverted = $color->invert();

		$this->assertNotSame($color, $inverted);

		$this->assertSame(255, $inverted->red);
		$this->assertSame(255, $inverted->green);
		$this->assertSame(255, $inverted->blue);
	}

	/**
	 *
	 */
	public function testComplementary(): void
	{
		$color = new Color(255, 0, 0);

		$complementary = $color->complementary();

		$this->assertNotSame($color, $complementary);

		$this->assertSame(0, $complementary->red);
		$this->assertSame(255, $complementary->green);
		$this->assertSame(255, $complementary->blue);

		//

		$color = new Color(128, 1, 254);

		$complementary = $color->complementary();

		$this->assertNotSame($color, $complementary);

		$this->assertSame(127, $complementary->red);
		$this->assertSame(254, $complementary->green);
		$this->assertSame(1, $complementary->blue);
	}

	/*
	 *
	 */
	public function testToHexString(): void
	{
		$color = new Color(255, 0, 0);

		$this->assertSame('#FF0000', $color->toHexString());

		$color = new Color(0, 255, 0);

		$this->assertSame('#00FF00', $color->toHexString());

		$color = new Color(0, 0, 255);

		$this->assertSame('#0000FF', $color->toHexString());
	}

	/*
	 *
	 */
	public function testToHexaString(): void
	{
		$color = new Color(255, 0, 0);

		$this->assertSame('#FF0000FF', $color->toHexaString());

		$color = new Color(255, 0, 0, 127);

		$this->assertSame('#FF00007F', $color->toHexaString());
	}

	/*
	 *
	 */
	public function testToRgbString(): void
	{
		$color = new Color(255, 0, 0);

		$this->assertSame('rgb(255, 0, 0)', $color->toRgbString());

		$color = new Color(0, 255, 0);

		$this->assertSame('rgb(0, 255, 0)', $color->toRgbString());

		$color = new Color(0, 0, 255);

		$this->assertSame('rgb(0, 0, 255)', $color->toRgbString());
	}

	/*
	 *
	 */
	public function testToRgbaString(): void
	{
		$color = new Color(255, 0, 0);

		$this->assertSame('rgba(255, 0, 0, 1.000)', $color->toRgbaString());

		$color = new Color(0, 255, 0);

		$this->assertSame('rgba(0, 255, 0, 1.000)', $color->toRgbaString());

		$color = new Color(0, 0, 255);

		$this->assertSame('rgba(0, 0, 255, 1.000)', $color->toRgbaString());

		//

		$color = new Color(255, 0, 0, 127);

		$this->assertSame('rgba(255, 0, 0, 0.498)', $color->toRgbaString(true));
	}

	/*
	 *
	 */
	public function testToHslString(): void
	{
		$color = new Color(255, 0, 0);

		$this->assertSame('hsl(0 100.0% 50.0%)', $color->toHslString());

		$color = new Color(0, 255, 0);

		$this->assertSame('hsl(120 100.0% 50.0%)', $color->toHslString());

		$color = new Color(0, 0, 255);

		$this->assertSame('hsl(240 100.0% 50.0%)', $color->toHslString());

		//

		$color = Color::fromHsl(0, 100.0, 50.0);

		$this->assertSame('hsl(0 100.0% 50.0%)', $color->toHslString());

		$color = Color::fromHsl(120, 100.0, 50.0);

		$this->assertSame('hsl(120 100.0% 50.0%)', $color->toHslString());

		$color = Color::fromHsl(240, 100.0, 50.0);

		$this->assertSame('hsl(240 100.0% 50.0%)', $color->toHslString());

		// Grayscale

		$color = new Color(192, 192, 192);

		$this->assertSame('hsla(0 0.0% 75.3% / 1.000)', $color->toHslaString());
	}

	/*
	 *
	 */
	public function testToHslaString(): void
	{
		$color = new Color(255, 0, 0);

		$this->assertSame('hsla(0 100.0% 50.0% / 1.000)', $color->toHslaString());

		$color = new Color(0, 255, 0);

		$this->assertSame('hsla(120 100.0% 50.0% / 1.000)', $color->toHslaString());

		$color = new Color(0, 0, 255);

		$this->assertSame('hsla(240 100.0% 50.0% / 1.000)', $color->toHslaString());

		//

		$color = new Color(255, 0, 0, 127);

		$this->assertSame('hsla(0 100.0% 50.0% / 0.498)', $color->toHslaString());
	}

	/*
	 *
	 */
	public function testToHwbString(): void
	{
		$color = new Color(255, 0, 0);

		$this->assertSame('hwb(0 0.0% 0.0%)', $color->toHwbString());

		$color = new Color(0, 255, 0);

		$this->assertSame('hwb(120 0.0% 0.0%)', $color->toHwbString());

		$color = new Color(0, 0, 255);

		$this->assertSame('hwb(240 0.0% 0.0%)', $color->toHwbString());

		//

		$color = Color::fromHwb(0, 0.0, 0.0);

		$this->assertSame('hwb(0 0.0% 0.0%)', $color->toHwbString());

		$color = Color::fromHwb(120, 0.0, 0.0);

		$this->assertSame('hwb(120 0.0% 0.0%)', $color->toHwbString());

		$color = Color::fromHwb(240, 0.0, 0.0);

		$this->assertSame('hwb(240 0.0% 0.0%)', $color->toHwbString());
	}

	/*
	 *
	 */
	public function testToHwbaString(): void
	{
		$color = new Color(255, 0, 0);

		$this->assertSame('hwb(0 0.0% 0.0% / 1.000)', $color->toHwbaString());

		$color = new Color(0, 255, 0);

		$this->assertSame('hwb(120 0.0% 0.0% / 1.000)', $color->toHwbaString());

		$color = new Color(0, 0, 255);

		$this->assertSame('hwb(240 0.0% 0.0% / 1.000)', $color->toHwbaString());

		//

		$color = new Color(255, 0, 0, 127);

		$this->assertSame('hwb(0 0.0% 0.0% / 0.498)', $color->toHwbaString());
	}

	/**
	 *
	 */
	public function testToColorFamily(): void
	{
		// Key = Color, Value = Expected Family

		$colors = [
			// Basic colors

			WebColor::Red->value => ColorFamily::Red,
			WebColor::Orange->value => ColorFamily::Orange,
			WebColor::Yellow->value => ColorFamily::Yellow,
			WebColor::Green->value => ColorFamily::Green,
			WebColor::Cyan->value => ColorFamily::Cyan,
			WebColor::Blue->value => ColorFamily::Blue,
			WebColor::Purple->value => ColorFamily::Purple,
			WebColor::Pink->value => ColorFamily::Pink,
			WebColor::Brown->value => ColorFamily::Brown,
			WebColor::Black->value => ColorFamily::Black,
			WebColor::Gray->value => ColorFamily::Gray,
			WebColor::White->value => ColorFamily::White,

			// Red

			WebColor::DarkRed->value => ColorFamily::Red,
			WebColor::Crimson->value => ColorFamily::Red,
			WebColor::IndianRed->value => ColorFamily::Red,

			// Orange

			WebColor::OrangeRed->value => ColorFamily::Orange,
			WebColor::DarkOrange->value => ColorFamily::Orange,
			WebColor::Coral->value => ColorFamily::Orange,

			// Yellow

			WebColor::Gold->value => ColorFamily::Yellow,
			WebColor::PaleGoldenrod->value => ColorFamily::Yellow,
			WebColor::LightYellow->value => ColorFamily::Yellow,
			WebColor::LightGoldenrodYellow->value => ColorFamily::Yellow,

			// Green

			WebColor::DarkGreen->value => ColorFamily::Green,
			WebColor::ForestGreen->value => ColorFamily::Green,
			WebColor::SeaGreen->value => ColorFamily::Green,
			WebColor::MediumSeaGreen->value => ColorFamily::Green,
			WebColor::LimeGreen->value => ColorFamily::Green,
			WebColor::SpringGreen->value => ColorFamily::Green,
			WebColor::MediumSpringGreen->value => ColorFamily::Green,
			WebColor::LawnGreen->value => ColorFamily::Green,
			WebColor::LightGreen->value => ColorFamily::Green,
			WebColor::PaleGreen->value => ColorFamily::Green,

			// Cyan

			WebColor::Teal->value => ColorFamily::Cyan,
			WebColor::DarkCyan->value => ColorFamily::Cyan,
			WebColor::DarkTurquoise->value => ColorFamily::Cyan,
			WebColor::Turquoise->value => ColorFamily::Cyan,
			WebColor::PaleTurquoise->value => ColorFamily::Cyan,
			WebColor::LightCyan->value => ColorFamily::Cyan,

			// Blue

			WebColor::MidnightBlue->value => ColorFamily::Blue,
			WebColor::Navy->value => ColorFamily::Blue,
			WebColor::DarkBlue->value => ColorFamily::Blue,
			WebColor::MediumBlue->value => ColorFamily::Blue,
			WebColor::RoyalBlue->value => ColorFamily::Blue,
			WebColor::SteelBlue->value => ColorFamily::Blue,
			WebColor::DodgerBlue->value => ColorFamily::Blue,
			WebColor::DeepSkyBlue->value => ColorFamily::Blue,
			WebColor::SkyBlue->value => ColorFamily::Blue,
			WebColor::LightSkyBlue->value => ColorFamily::Blue,

			// Purple

			WebColor::Indigo->value => ColorFamily::Purple,
			WebColor::DarkMagenta->value => ColorFamily::Purple,
			WebColor::DarkViolet->value => ColorFamily::Purple,
			WebColor::BlueViolet->value => ColorFamily::Purple,
			WebColor::DarkOrchid->value => ColorFamily::Purple,
			WebColor::MediumOrchid->value => ColorFamily::Purple,
			WebColor::MediumPurple->value => ColorFamily::Purple,
			WebColor::Orchid->value => ColorFamily::Purple,
			WebColor::Violet->value => ColorFamily::Purple,
			WebColor::Thistle->value => ColorFamily::Purple,

			// Pink

			WebColor::PaleVioletRed->value => ColorFamily::Pink,
			WebColor::HotPink->value => ColorFamily::Pink,
			WebColor::LightPink->value => ColorFamily::Pink,

			// Brown

			WebColor::SaddleBrown->value => ColorFamily::Brown,
			WebColor::Sienna->value => ColorFamily::Brown,

			// Gray

			WebColor::DimGray->value => ColorFamily::Gray,
			WebColor::DarkGray->value => ColorFamily::Gray,
			WebColor::Silver->value => ColorFamily::Gray,
			WebColor::LightGray->value => ColorFamily::Gray,
			WebColor::Gainsboro->value => ColorFamily::Gray,

			// White

			WebColor::WhiteSmoke->value => ColorFamily::White,
			WebColor::Seashell->value => ColorFamily::White,
			WebColor::GhostWhite->value => ColorFamily::White,
			WebColor::Honeydew->value => ColorFamily::White,
			WebColor::MintCream->value => ColorFamily::White,
			WebColor::Snow->value => ColorFamily::White,
			WebColor::Ivory->value => ColorFamily::White,
		];

		foreach ($colors as $color => $expected) {
			$this->assertSame(
				$expected,
				Color::fromHex($color)->toColorFamily(),
				"Color {$color} should have been identified as {$expected->name}."
			);
		}
	}
}
