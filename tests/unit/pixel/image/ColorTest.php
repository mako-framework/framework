<?php

namespace mako\tests\unit\pixel\image;

use InvalidArgumentException;
use mako\pixel\image\Color;
use mako\tests\TestCase;

class ColorTest extends TestCase
{
	/**
	 *
	 */
	public function testConstructorWithValidArguments(): void
	{
		$color = new Color(50, 100, 150, 200);

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

		$this->expectExceptionMessage('Red must be between 0 and 255.');

		$color = new Color(300, 255, 255);
	}

	/**
	 *
	 */
	public function testConstructorWithInvaliGreenArgument(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$this->expectExceptionMessage('Green must be between 0 and 255.');

		$color = new Color(255, 300, 255);
	}

	/**
	 *
	 */
	public function testConstructorWithInvaliBlueArgument(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$this->expectExceptionMessage('Blue must be between 0 and 255.');

		$color = new Color(255, 255, 300);
	}

	/**
	 *
	 */
	public function testConstructorWithInvaliAlphaArgument(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$this->expectExceptionMessage('Alpha must be between 0 and 255.');

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

		$this->expectExceptionMessage('Invalid hex color format.');

		$color = Color::fromHex('foobar');
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

		//

		$color = new Color(255, 0, 0);

		$this->assertSame('#FF0000FF', $color->toHexString(true));

		$color = new Color(255, 0, 0, 127);

		$this->assertSame('#FF00007F', $color->toHexString(true));
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

		$this->assertSame('hsl(0, 100.0%, 50.0%)', $color->toHslString());

		$color = new Color(0, 255, 0);

		$this->assertSame('hsl(120, 100.0%, 50.0%)', $color->toHslString());

		$color = new Color(0, 0, 255);

		$this->assertSame('hsl(240, 100.0%, 50.0%)', $color->toHslString());
	}

	/*
	 *
	 */
	public function testToHslaString(): void
	{
		$color = new Color(255, 0, 0);

		$this->assertSame('hsla(0, 100.0%, 50.0%, 1.000)', $color->toHslaString());

		$color = new Color(0, 255, 0);

		$this->assertSame('hsla(120, 100.0%, 50.0%, 1.000)', $color->toHslaString());

		$color = new Color(0, 0, 255);

		$this->assertSame('hsla(240, 100.0%, 50.0%, 1.000)', $color->toHslaString());

		//

		$color = new Color(255, 0, 0, 127);

		$this->assertSame('hsla(0, 100.0%, 50.0%, 0.498)', $color->toHslaString());
	}
}
