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
	 *
	 * @param string                                   $image     Path to image file
	 * @param \mako\pixl\processors\ProcessorInterface $processor Processor instance
	 */
	public function __construct(
		protected $image,
		protected ProcessorInterface $processor
	)
	{
		// Make sure that the image exists

		if(file_exists($this->image) === false)
		{
			throw new PixlException(vsprintf('The image [ %s ] does not exist.', [$this->image]));
		}

		// Set the image

		$this->processor->open($image);
	}

	/**
	 * Makes sure that the quality is between 1 and 100.
	 *
	 * @param  int $quality Image quality
	 * @return int
	 */
	protected function normalizeImageQuality($quality)
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
	 *
	 * @return int
	 */
	public function getWidth()
	{
		return $this->processor->getWidth();
	}

	/**
	 * Returns the image height in pixels.
	 *
	 * @return int
	 */
	public function getHeight()
	{
		return $this->processor->getHeight();
	}

	/**
	 * Returns an array containing the image dimensions in pixels.
	 *
	 * @return array
	 */
	public function getDimensions()
	{
		return $this->processor->getDimensions();
	}

	/**
	 * Rotates the image using the given angle in degrees.
	 *
	 * @param  int              $degrees Degrees to rotate the image
	 * @return \mako\pixl\Image
	 */
	public function rotate($degrees): Image
	{
		$this->processor->rotate($degrees);

		return $this;
	}

	/**
	 * Resizes the image to the chosen size.
	 *
	 * @param  int              $width       Width of the image
	 * @param  int              $height      Height of the image
	 * @param  int              $aspectRatio Aspect ratio
	 * @return \mako\pixl\Image
	 */
	public function resize($width, $height = null, $aspectRatio = Image::RESIZE_IGNORE): Image
	{
		$this->processor->resize($width, $height, $aspectRatio);

		return $this;
	}

	/**
	 * Crops the image.
	 *
	 * @param  int              $width  Width of the crop
	 * @param  int              $height Height of the crop
	 * @param  int              $x      The X coordinate of the cropped region's top left corner
	 * @param  int              $y      The Y coordinate of the cropped region's top left corner
	 * @return \mako\pixl\Image
	 */
	public function crop($width, $height, $x, $y): Image
	{
		$this->processor->crop($width, $height, $x, $y);

		return $this;
	}

	/**
	 * Flips the image.
	 *
	 * @param  int              $direction Direction to flip the image
	 * @return \mako\pixl\Image
	 */
	public function flip($direction = Image::FLIP_HORIZONTAL): Image
	{
		$this->processor->flip($direction);

		return $this;
	}

	/**
	 * Adds a watermark to the image.
	 *
	 * @param  string           $file     Path to the image file
	 * @param  int              $position Position of the watermark
	 * @param  int              $opacity  Opacity of the watermark in percent
	 * @return \mako\pixl\Image
	 */
	public function watermark($file, $position = Image::WATERMARK_TOP_LEFT, $opacity = 100): Image
	{
		// Check if the image exists

		if(file_exists($file) === false)
		{
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
	 *
	 * @param  int              $level Brightness level (-100 to 100)
	 * @return \mako\pixl\Image
	 */
	public function brightness($level = 50): Image
	{
		// Normalize brighness level

		$level = min(max($level, -100), 100);

		// Adjust brightness

		$this->processor->brightness($level);

		return $this;
	}

	/**
	 * Converts image to greyscale.
	 *
	 * @return \mako\pixl\Image
	 */
	public function greyscale(): Image
	{
		$this->processor->greyscale();

		return $this;
	}

	/**
	 * Converts image to sepia.
	 *
	 * @return \mako\pixl\Image
	 */
	public function sepia(): Image
	{
		$this->processor->sepia();

		return $this;
	}

	/**
	 * Converts image to bitonal.
	 *
	 * @return \mako\pixl\Image
	 */
	public function bitonal(): Image
	{
		$this->processor->bitonal();

		return $this;
	}

	/**
	 * Colorizes the image.
	 *
	 * @param  string           $color Hex code for the color
	 * @return \mako\pixl\Image
	 */
	public function colorize($color): Image
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
	 *
	 * @param  int              $pixelSize Pixel size
	 * @return \mako\pixl\Image
	 */
	public function pixelate($pixelSize = 10): Image
	{
		$this->processor->pixelate($pixelSize);

		return $this;
	}

	/**
	 * Negates the image.
	 *
	 * @return \mako\pixl\Image
	 */
	public function negate(): Image
	{
		$this->processor->negate();

		return $this;
	}

	/**
	 * Adds a border to the image.
	 *
	 * @param  string           $color     Hex code for the color
	 * @param  int              $thickness Thickness of the frame in pixels
	 * @return \mako\pixl\Image
	 */
	public function border($color = '#000', $thickness = 5): Image
	{
		$this->processor->border($color, $thickness);

		return $this;
	}

	/**
	 * Returns a string containing the image.
	 *
	 * @param  string $type    Image type
	 * @param  int    $quality Image quality 1-100
	 * @return string
	 */
	public function getImageBlob($type = null, $quality = 95)
	{
		return $this->processor->getImageBlob($type, $this->normalizeImageQuality($quality));
	}

	/**
	 * Saves image to file.
	 *
	 * @param string $file    Path to the image file
	 * @param int    $quality Image quality 1-100
	 */
	public function save($file = null, $quality = 95): void
	{
		$file ??= $this->image;

		// Mage sure that the file or directory is writable

		if(file_exists($file))
		{
			if(!is_writable($file))
			{
				throw new PixlException(vsprintf('The file [ %s ] isn\'t writable.', [$file]));
			}
		}
		else
		{
			$pathInfo = pathinfo($file);

			if(!is_writable($pathInfo['dirname']))
			{
				throw new PixlException(vsprintf('The directory [ %s ] isn\'t writable.', [$pathInfo['dirname']]));
			}
		}

		// Save the image

		$this->processor->save($file, $this->normalizeImageQuality($quality));
	}
}
