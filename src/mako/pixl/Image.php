<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixl;

use mako\pixl\exceptions\PixlException;
use mako\pixl\processors\ProcessorInterface;

use function file_exists;
use function is_writable;
use function max;
use function min;
use function pathinfo;
use function vsprintf;

/**
 * Image manipulation class.
 */
class Image
{
	/**
	 * Resizing constraint.
	 *
	 * @var int
	 */
	public const RESIZE_IGNORE = 10;

	/**
	 * Resizing constraint.
	 *
	 * @var int
	 */
	public const RESIZE_AUTO = 11;

	/**
	 * Resizing constraint.
	 *
	 * @var int
	 */
	public const RESIZE_WIDTH = 12;

	/**
	 * Resizing constraint.
	 *
	 * @var int
	 */
	public const RESIZE_HEIGHT = 13;

	/**
	 * Watermark position.
	 *
	 * @var int
	 */
	public const WATERMARK_TOP_LEFT = 20;

	/**
	 * Watermark position.
	 *
	 * @var int
	 */
	public const WATERMARK_TOP_RIGHT = 21;

	/**
	 * Watermark position.
	 *
	 * @var int
	 */
	public const WATERMARK_BOTTOM_LEFT = 22;

	/**
	 * Watermark position.
	 *
	 * @var int
	 */
	public const WATERMARK_BOTTOM_RIGHT = 23;

	/**
	 * Watermark position.
	 *
	 * @var int
	 */
	public const WATERMARK_CENTER = 24;

	/**
	 * Flip direction.
	 *
	 * @var int
	 */
	public const FLIP_VERTICAL = 30;

	/**
	 * Flip direction.
	 *
	 * @var int
	 */
	public const FLIP_HORIZONTAL = 31;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected $image,
		protected ProcessorInterface $processor
	) {
		// Make sure that the image exists

		if (file_exists($this->image) === false) {
			throw new PixlException(vsprintf('The image [ %s ] does not exist.', [$this->image]));
		}

		// Set the image

		$this->processor->open($image);
	}

	/**
	 * Makes sure that the quality is between 1 and 100.
	 */
	protected function normalizeImageQuality(int $quality): int
	{
		return max(min((int) $quality, 100), 1);
	}

	/**
	 * Creates a snapshot of the image.
	 */
	public function snapshot(): void
	{
		$this->processor->snapshot();
	}

	/**
	 * Retstores the image snapshot.
	 */
	public function restore(): void
	{
		$this->processor->restore();
	}

	/**
	 * Returns the image width in pixels.
	 */
	public function getWidth(): int
	{
		return $this->processor->getWidth();
	}

	/**
	 * Returns the image height in pixels.
	 */
	public function getHeight(): int
	{
		return $this->processor->getHeight();
	}

	/**
	 * Returns an array containing the image dimensions in pixels.
	 */
	public function getDimensions(): array
	{
		return $this->processor->getDimensions();
	}

	/**
	 * Rotates the image using the given angle in degrees.
	 */
	public function rotate(int $degrees): Image
	{
		$this->processor->rotate($degrees);

		return $this;
	}

	/**
	 * Resizes the image to the chosen size.
	 */
	public function resize(int $width, ?int $height = null, int $aspectRatio = Image::RESIZE_IGNORE): Image
	{
		$this->processor->resize($width, $height, $aspectRatio);

		return $this;
	}

	/**
	 * Crops the image.
	 */
	public function crop(int $width, int $height, int $x, int $y): Image
	{
		$this->processor->crop($width, $height, $x, $y);

		return $this;
	}

	/**
	 * Flips the image.
	 */
	public function flip(int $direction = Image::FLIP_HORIZONTAL): Image
	{
		$this->processor->flip($direction);

		return $this;
	}

	/**
	 * Adds a watermark to the image.
	 */
	public function watermark(string $file, int $position = Image::WATERMARK_TOP_LEFT, int $opacity = 100): Image
	{
		// Check if the image exists

		if (file_exists($file) === false) {
			throw new PixlException(vsprintf('The watermark image [ %s ] does not exist.', [$file]));
		}

		// Make sure that opacity is between 0 and 100

		$opacity = max(min((int) $opacity, 100), 0);

		// Add watermark to the image

		$this->processor->watermark($file, $position, $opacity);

		return $this;
	}

	/**
	 * Adjust image brightness.
	 */
	public function brightness(int $level = 50): Image
	{
		// Normalize brighness level

		$level = min(max($level, -100), 100);

		// Adjust brightness

		$this->processor->brightness($level);

		return $this;
	}

	/**
	 * Converts image to greyscale.
	 */
	public function greyscale(): Image
	{
		$this->processor->greyscale();

		return $this;
	}

	/**
	 * Converts image to sepia.
	 */
	public function sepia(): Image
	{
		$this->processor->sepia();

		return $this;
	}

	/**
	 * Converts image to bitonal.
	 */
	public function bitonal(): Image
	{
		$this->processor->bitonal();

		return $this;
	}

	/**
	 * Colorizes the image.
	 */
	public function colorize(string $color): Image
	{
		$this->processor->colorize($color);

		return $this;
	}

	/**
	 * Sharpens the image.
	 */
	public function sharpen(): Image
	{
		$this->processor->sharpen();

		return $this;
	}

	/**
	 * Pixelates the image.
	 */
	public function pixelate(int $pixelSize = 10): Image
	{
		$this->processor->pixelate($pixelSize);

		return $this;
	}

	/**
	 * Negates the image.
	 */
	public function negate(): Image
	{
		$this->processor->negate();

		return $this;
	}

	/**
	 * Adds a border to the image.
	 */
	public function border(string $color = '#000', int $thickness = 5): Image
	{
		$this->processor->border($color, $thickness);

		return $this;
	}

	/**
	 * Returns a string containing the image.
	 */
	public function getImageBlob(?string $type = null, int $quality = 95): string
	{
		return $this->processor->getImageBlob($type, $this->normalizeImageQuality($quality));
	}

	/**
	 * Saves image to file.
	 */
	public function save(?string $file = null, int $quality = 95): void
	{
		$file ??= $this->image;

		// Mage sure that the file or directory is writable

		if (file_exists($file)) {
			if (!is_writable($file)) {
				throw new PixlException(vsprintf('The file [ %s ] isn\'t writable.', [$file]));
			}
		}
		else {
			$pathInfo = pathinfo($file);

			if (!is_writable($pathInfo['dirname'])) {
				throw new PixlException(vsprintf('The directory [ %s ] isn\'t writable.', [$pathInfo['dirname']]));
			}
		}

		// Save the image

		$this->processor->save($file, $this->normalizeImageQuality($quality));
	}
}
