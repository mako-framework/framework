<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\pixl;

use RuntimeException;

use mako\pixl\processors\ProcessorInterface;

/**
 * Image manipulation class.
 *
 * @author  Frederic G. Østby
 */

class Image
{
	/**
	 * Resizing constraint.
	 *
	 * @var int
	 */

	const RESIZE_IGNORE = 10;

	/**
	 * Resizing constraint.
	 *
	 * @var int
	 */

	const RESIZE_AUTO = 11;

	/**
	 * Resizing constraint.
	 *
	 * @var int
	 */

	const RESIZE_WIDTH = 12;

	/**
	 * Resizing constraint.
	 *
	 * @var int
	 */

	const RESIZE_HEIGHT = 13;

	/**
	 * Watermark position.
	 *
	 * @var int
	 */

	const WATERMARK_TOP_LEFT = 20;

	/**
	 * Watermark position.
	 *
	 * @var int
	 */

	const WATERMARK_TOP_RIGHT = 21;

	/**
	 * Watermark position.
	 *
	 * @var int
	 */

	const WATERMARK_BOTTOM_LEFT = 22;

	/**
	 * Watermark position.
	 *
	 * @var int
	 */

	const WATERMARK_BOTTOM_RIGHT = 23;

	/**
	 * Watermark position.
	 *
	 * @var int
	 */

	const WATERMARK_CENTER = 24;

	/**
	 * Flip direction.
	 *
	 * @var int
	 */

	const FLIP_VERTICAL = 30;

	/**
	 * Flip direction.
	 *
	 * @var int
	 */

	const FLIP_HORIZONTAL = 31;

	/**
	 * Processor instance.
	 *
	 * @var \mako\pixl\processors\ProcessorInterface
	 */

	protected $processor;

	/**
	 * Path to image file.
	 *
	 * @var string
	 */

	protected $image;

	/**
	 * Constructor.
	 *
	 * @access  public
	 *
	 * @param   string                                    $image      Path to image file
	 * @param   \mako\pixl\processors\ProcessorInterface  $processor  Processor instance
	 */

	public function __construct($image, ProcessorInterface $processor)
	{
		$this->image = $image;

		$this->processor = $processor;

		// Make sure that the image exists

		if(file_exists($this->image) === false)
		{
			throw new RuntimeException(vsprintf("%s(): The image [ %s ] does not exist.", [__METHOD__, $this->image]));
		}

		// Set the image

		$this->processor->open($image);
	}

	/**
	 * Makes sure that the quality is between 1 and 100.
	 *
	 * @access  protected
	 * @param   int        $quality  Image quality
	 * @return  int
	 */

	protected function normalizeImageQuality($quality)
	{
		return max(min((int) $quality, 100), 1);
	}

	/**
	 * Creates a snapshot of the image.
	 *
	 * @access  public
	 */

	public function snapshot()
	{
		$this->processor->snapshot();
	}

	/**
	 * Retstores the image snapshot.
	 *
	 * @access  public
	 */

	public function restore()
	{
		$this->processor->restore();
	}

	/**
	 * Returns the image width in pixels.
	 *
	 * @access  public
	 * @return  int
	 */

	public function getWidth()
	{
		return $this->processor->getWidth();
	}

	/**
	 * Returns the image height in pixels.
	 *
	 * @access  public
	 * @return  int
	 */

	public function getHeight()
	{
		return $this->processor->getHeight();
	}

	/**
	 * Returns an array containing the image dimensions in pixels.
	 *
	 * @access  public
	 * @return  array
	 */

	public function getDimensions()
	{
		return $this->processor->getDimensions();
	}

	/**
	 * Rotates the image using the given angle in degrees.
	 *
	 * @access  public
	 * @param   int               $degrees  Degrees to rotate the image
	 * @return  \mako\pixl\Image
	 */

	public function rotate($degrees)
	{
		$this->processor->rotate($degrees);

		return $this;
	}

	/**
	 * Resizes the image to the chosen size.
	 *
	 * @param   int               $width        Width of the image
	 * @param   int               $height       Height of the image
	 * @param   int               $aspectRatio  Aspect ratio
	 * @return  \mako\pixl\Image
	 */

	public function resize($width, $height = null, $aspectRatio = Image::RESIZE_IGNORE)
	{
		$this->processor->resize($width, $height, $aspectRatio);

		return $this;
	}

	/**
	 * Crops the image.
	 *
	 * @access  public
	 * @param   int               $width   Width of the crop
	 * @param   int               $height  Height of the crop
	 * @param   int               $x       The X coordinate of the cropped region's top left corner
	 * @param   int               $y       The Y coordinate of the cropped region's top left corner
	 * @return  \mako\pixl\Image
	 */

	public function crop($width, $height, $x, $y)
	{
		$this->processor->crop($width, $height, $x, $y);

		return $this;
	}

	/**
	 * Flips the image.
	 *
	 * @access  public
	 * @param   int               $direction  Direction to flip the image
	 * @return  \mako\pixl\Image
	 */

	public function flip($direction = Image::FLIP_HORIZONTAL)
	{
		$this->processor->flip($direction);

		return $this;
	}

	/**
	 * Adds a watermark to the image.
	 *
	 * @access  public
	 * @param   string            $file      Path to the image file
	 * @param   int               $position  Position of the watermark
	 * @param   int               $opacity   Opacity of the watermark in percent
	 * @return  \mako\pixl\Image
	 */

	public function watermark($file, $position = Image::WATERMARK_TOP_LEFT, $opacity = 100)
	{
		// Check if the image exists

		if(file_exists($file) === false)
		{
			throw new RuntimeException(vsprintf("%s(): The watermark image [ %s ] does not exist.", [__METHOD__, $file]));
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
	 * @access  public
	 * @param   int               $level  Brightness level (-100 to 100)
	 * @return  \mako\pixl\Image
	 */

	public function brightness($level = 50)
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
	 * @access  public
	 * @return  \mako\pixl\Image
	 */

	public function greyscale()
	{
		$this->processor->greyscale();

		return $this;
	}

	/**
	 * Converts image to sepia.
	 *
	 * @access  public
	 * @return  \mako\pixl\Image
	 */

	public function sepia()
	{
		$this->processor->sepia();

		return $this;
	}

	/**
	 * Colorizes the image.
	 *
	 * @access  public
	 * @param   string            $color  Hex code for the color
	 * @return  \mako\pixl\Image
	 */

	public function colorize($color)
	{
		$this->processor->colorize($color);

		return $this;
	}

	/**
	 * Sharpens the image.
	 *
	 * @access  public
	 */

	public function sharpen()
	{
		$this->processor->sharpen();

		return $this;
	}

	/**
	 * Pixelates the image.
	 *
	 * @access  public
	 * @param   int               $pixelSize  Pixel size
	 * @return  \mako\pixl\Image
	 */

	public function pixelate($pixelSize = 10)
	{
		$this->processor->pixelate($pixelSize);

		return $this;
	}

	/**
	 * Negates the image.
	 *
	 * @access  public
	 * @return  \mako\pixl\Image
	 */

	public function negate()
	{
		$this->processor->negate();

		return $this;
	}

	/**
	 * Adds a border to the image.
	 *
	 * @access  public
	 * @param   string            $color      Hex code for the color
	 * @param   int               $thickness  Thickness of the frame in pixels
	 * @return  \mako\pixl\Image
	 */

	public function border($color = '#000', $thickness = 5)
	{
		$this->processor->border($color, $thickness);

		return $this;
	}

	/**
	 * Returns a string containing the image.
	 *
	 * @access  public
	 * @param   string  $type     Image type
	 * @param   int     $quality  Image quality 1-100
	 * @return  string
	 */

	public function getImageBlob($type = null, $quality = 95)
	{
		return $this->processor->getImageBlob($type, $this->normalizeImageQuality($quality));
	}

	/**
	 * Saves image to file.
	 *
	 * @access  public
	 * @param   string  $file     Path to the image file
	 * @param   int     $quality  Image quality 1-100
	 */

	public function save($file = null, $quality = 95)
	{
		$file = $file ?: $this->image;

		// Mage sure that the file or directory is writable

		if(file_exists($file))
		{
			if(!is_writable($file))
			{
				throw new RuntimeException(vsprintf("%s(): The file [ %s ] isn't writable.", [__METHOD__, $file]));
			}
		}
		else
		{
			$pathInfo = pathinfo($file);

			if(!is_writable($pathInfo['dirname']))
			{
				throw new RuntimeException(vsprintf("%s(): The directory [ %s ] isn't writable.", [__METHOD__, $pathInfo['dirname']]));
			}
		}

		// Save the image

		$this->processor->save($file, $this->normalizeImageQuality($quality));
	}
}