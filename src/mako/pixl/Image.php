<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\pixl;

use \RuntimeException;

use \mako\pixl\processors\ProcessorInterface;

/**
 * Image manipulation class.
 *
 * @author  Frederic G. Østby
 */

class Image
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------
	
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

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

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

		$this->imageCheck();

		// Set the image

		$this->processor->setImage($image);
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Checks if the image exists and throw a RuntimeException if it doesn't.
	 * 
	 * @access  protected
	 */

	protected function imageCheck()
	{
		if(file_exists($this->image) === false)
		{
			throw new RuntimeException(vsprintf("%s(): The image [ %s ] does not exist.", [__METHOD__, $this->image]));
		}
	}

	/**
	 * Checks that the file or directory is writable and throw a RuntimeException if it isn't.
	 * 
	 * @access  protected
	 * @param   string     $path  Path to the image
	 */

	protected function writeCheck($path)
	{
		if(file_exists($path))
		{
			if(!is_writable($path))
			{
				throw new RuntimeException(vsprintf("%s(): The file [ %s ] isn't writable.", [__METHOD__, $path]));
			}
		}
		else
		{
			$pathInfo = pathinfo($path);

			if(!is_writable($pathInfo['dirname']))
			{
				throw new RuntimeException(vsprintf("%s(): The directory [ %s ] isn't writable.", [__METHOD__, $pathInfo['dirname']]));
			}
		}
	}

	/**
	 * Makes sure that the quality is between 0 and 100.
	 * 
	 * @access  protected
	 * @param   int        $quality  Image quality
	 * @return  int
	 */

	protected function normalizeImageQuality($quality)
	{
		$quality = max(min((int) $quality, 100), 0);
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
	 * @param   int               $height       (optional) Height of the image
	 * @param   int               $aspectRatio  (optional) Aspect ratio
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
	 * @param   int               $direction  (optional) Direction to flip the image
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
	 * @param   int               $position  (optional) Position of the watermark
	 * @param   int               $opacity   (optional) Opacity of the watermark in percent
	 * @return  \mako\pixl\Image
	 */

	public function watermark($file, $position = Image::WATERMARK_TOP_LEFT, $opacity = 100)
	{
		// Make sure that opacity is between 0 and 100
		
		$opacity = max(min((int) $opacity, 100), 0);

		// Add watermark to the image

		$this->processor->watermark($file, $position, $opacity);

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
	 * Colorize an image.
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
	 * @param   string  $type     (optional) Image type
	 * @param   int     $quality  (optional) Image quality 1-100
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
	 * @param   int     $quality  (optional) Image quality 1-100
	 */

	public function save($file, $quality = 95)
	{
		// Mage sure that the file or directory is writable

		$this->writeCheck($file);

		// Save the image

		$this->processor->save($file, $this->normalizeImageQuality($quality));
	}
}