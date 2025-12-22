<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image;

use InvalidArgumentException;

use function abs;
use function fmod;
use function hexdec;
use function ltrim;
use function max;
use function min;
use function preg_match;
use function round;
use function sprintf;
use function strlen;
use function substr;

/**
 * Color.
 */
class Color
{
	/**
	 * Constructor.
	 */
	final public function __construct(
		protected int $red {
			set(int $value) {
				if ($value < 0 || $value > 255) {
					throw new InvalidArgumentException('Red must be between 0 and 255.');
				}
				$this->red = $value;
			}
		},
		protected int $green {
			set(int $value) {
				if ($value < 0 || $value > 255) {
					throw new InvalidArgumentException('Green must be between 0 and 255.');
				}
				$this->green = $value;
			}
		},
		protected int $blue {
			set(int $value) {
				if ($value < 0 || $value > 255) {
					throw new InvalidArgumentException('Blue must be between 0 and 255.');
				}
				$this->blue = $value;
			}
		},
		protected int $alpha = 255 {
			set(int $value) {
				if ($value < 0 || $value > 255) {
					throw new InvalidArgumentException('Alpha must be between 0 and 255.');
				}
				$this->alpha = $value;
			}
		}
	) {
	}

	/**
	 * Returns a Color instance based on a hex value.
	 */
	public static function fromHex(string $hex): static
	{
		$hex = ltrim($hex, '#');

		if (preg_match('/^[0-9a-fA-F]{6}([0-9a-fA-F]{2})?$/', $hex) !== 1) {
			throw new InvalidArgumentException('Invalid hex color format.');
		}

		$red   = hexdec(substr($hex, 0, 2));
		$green = hexdec(substr($hex, 2, 2));
		$blue  = hexdec(substr($hex, 4, 2));
		$alpha = strlen($hex) === 8 ? hexdec(substr($hex, 6, 2)) : 255;

		return new static($red, $green, $blue, $alpha);
	}

	/**
	 * Returns the red value of the color.
	 */
	public function getRed(): int
	{
		return $this->red;
	}

	/**
	 * Returns the green value of the color.
	 */
	public function getGreen(): int
	{
		return $this->green;
	}

	/**
	 * Returns the blue value of the color.
	 */
	public function getBlue(): int
	{
		return $this->blue;
	}

	/**
	 * Returns the alpha value of the color.
	 */
	public function getAlpha(): int
	{
		return $this->alpha;
	}

	/**
	 * Returns a hex string representation of the color.
	 */
	public function toHexString(bool $withAlpha = false): string
	{
		$hex = sprintf('#%02X%02X%02X', $this->red, $this->green, $this->blue);

		if ($withAlpha) {
			$hex .= sprintf('%02X', $this->alpha);
		}

		return $hex;
	}

	/**
	 * Returns a RGB string representation of the color.
	 */
	public function toRgbString(): string
	{
		return sprintf('rgb(%d, %d, %d)', $this->red, $this->green, $this->blue);
	}

	/**
	 * Returns a RGBA string representation of the color.
	 */
	public function toRgbaString(): string
	{
		return sprintf('rgba(%d, %d, %d, %.3f)', $this->red, $this->green, $this->blue, $this->alpha / 255);
	}

	/**
	 * Returns a HSL representation of the color.
	 */
	protected function toHsl(): array
	{
		$r = $this->red / 255;
		$g = $this->green / 255;
		$b = $this->blue / 255;

		$max = max($r, $g, $b);
		$min = min($r, $g, $b);
		$delta = $max - $min;

		$h = 0;
		$s = 0;
		$l = ($max + $min) / 2;

		if ($delta !== 0) {
			$s = $delta / (1 - abs(2 * $l - 1));

			match ($max) {
				$r => $h = 60 * fmod((($g - $b) / $delta), 6),
				$g => $h = 60 * ((($b - $r) / $delta) + 2),
				$b => $h = 60 * ((($r - $g) / $delta) + 4),
			};

			if ($h < 0) {
				$h += 360;
			}
		}

		return [$h, $s, $l];
	}

	/**
	 * Returns a HSL string representation of the color.
	 */
	public function toHslString(): string
	{
		[$h, $s, $l] = $this->toHsl();

		return sprintf('hsl(%d, %.1f%%, %.1f%%)', round($h), $s * 100, $l * 100);
	}

	/**
	 * Returns a HSLA string representation of the color.
	 */
	public function toHslaString(): string
	{
		[$h, $s, $l] = $this->toHsl();

		return sprintf('hsla(%d, %.1f%%, %.1f%%, %.3f)', round($h), $s * 100, $l * 100, $this->alpha / 255);
	}
}
