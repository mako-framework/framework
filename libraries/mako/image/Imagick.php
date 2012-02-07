<?php

namespace mako\image;

use \ImagickPixel;
use \Imagick as PHP_Imagick;
use \mako\Image;
use \RuntimeException;

/**
* Class that manipulates images using Imagick.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Imagick extends \mako\image\Adapter
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//------------------------------------------------

	/**
	* Constructor.
	*
	* @access  public
	* @param   string  Path to the image file
	*/

	public function __construct($file)
	{
		static $check = false;

		// Check if all the requirements are met

		if($check === false)
		{	
			if(class_exists('\Imagick', false) === false)
			{
				throw new RuntimeException(vsprintf("%s(): Imagick is not available.", array(__METHOD__)));
			}

			$check = true;
		}
		
		// Create image

		$this->image = new PHP_Imagick($file);
	}

	/**
	* Destructor.
	*
	* @access  public
	*/

	public function __destruct()
	{
		$this->image->destroy();
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	* Rotates the image using the given angle in degrees.
	*
	* @access  public
	* @param   int                 Degrees to rotate the image
	* @return  mako\image\Imagick
	*/

	public function rotate($degrees)
	{
		$this->image->rotateImage(new ImagickPixel('none'), (360 - $degrees));

		return $this;
	}

	/**
	* Resizes the image to the chosen size. 
	*
	* @param   int                 Width of the image
	* @param   int                 (optional) Height of the image
	* @param   int                 (optional) Aspect ratio
	* @return  mako\image\Imagick
	*/

	public function resize($width, $height = null, $aspectRatio = null)
	{
		$w = $this->image->getImageWidth();
		$h = $this->image->getImageHeight();

		if($height === null)
		{				
			$newWidth  = round($w * ($width / 100));
			$newHeight = round($h * ($width / 100));
		}
		else
		{
			if($aspectRatio === Image::AUTO)
			{
				// Calculate smallest size based on given height and width while maintaining aspect ratio

				$percentage = min(($width / $w), ($height / $h));

				$newWidth  = round($w * $percentage);
				$newHeight = round($h * $percentage);
			}
			else if($aspectRatio === Image::WIDTH)
			{
				// Base new size on given width while maintaining aspect ratio

				$newWidth  = $width;
				$newHeight = round($h * ($width / $w));
			}
			else if($aspectRatio === Image::HEIGHT)
			{
				// Base new size on given height while maintaining aspect ratio

				$newWidth  = round($w * ($height / $h));
				$newHeight = $height;
			}
			else
			{
				// Ignone aspect ratio
				
				$newWidth  = $width;
				$newHeight = $height;
			}					
		}
		
		$this->image->scaleImage($newWidth, $newHeight);
		
		return $this;
	}

	/**
	* Crops the image.
	*
	* @access  public
	* @param   int                 Width of the crop
	* @param   int                 Height of the crop
	* @param   int                 The X coordinate of the cropped region's top left corner
	* @param   int                 The Y coordinate of the cropped region's top left corner
	* @return  mako\image\Imagick
	*/

	public function crop($width, $height, $x, $y)
	{			
		$this->image->cropImage($width, $height, $x, $y);
		
		return $this;
	}

	/**
	* Flips the image.
	*
	* @access  public  
	* @param   int                 (optional) Direction to flip the image
	* @return  mako\image\Imagick
	*/

	public function flip($direction = null)
	{
		if($direction ===  Image::VERTICAL)
		{
			// Flips the image in the vertical direction

			$this->image->flipImage();
		}
		else
		{
			// Flips the image in the horizontal direction

			$this->image->flopImage();
		}

		return $this;
	}
	
	/**
	* Adds a watermark to the image.
	*
	* @access  public
	* @param   string              Path to the image file
	* @param   int                 (optional) Position of the watermark
	* @param   int                 (optional) Opacity of the watermark in percent
	* @return  mako\image\Imagick
	*/
	
	public function watermark($file, $position = null, $opacity = 100)
	{
		// Check if the image exists

		if(file_exists($file) === false)
		{
			throw new RuntimeException(vsprintf("%s(): The image file ('%s') does not exist.", array(__METHOD__, $file)));
		}
		
		$watermark = new PHP_Imagick($file);
		
		$watermarkW = $watermark->getImageWidth();
		$watermarkH = $watermark->getImageHeight();
		
		// Make sure that opacity is between 0 and 100
		
		$opacity = max(min((int) $opacity, 100), 0);
		
		if($opacity < 100)
		{				
			$watermark->evaluateImage(PHP_Imagick::EVALUATE_MULTIPLY, ($opacity / 100), PHP_Imagick::CHANNEL_ALPHA);
		}
		
		// Position the watermark.
		
		switch($position)
		{
			case Image::TOP_RIGHT:
				$x = $this->image->getImageWidth() - $watermarkW;
				$y = 0;
			break;
			case Image::BOTTOM_LEFT:
				$x = 0;
				$y = $this->image->getImageHeight() - $watermarkH;
			break;
			case Image::BOTTOM_RIGHT:
				$x = $this->image->getImageWidth() - $watermarkW;
				$y = $this->image->getImageHeight() - $watermarkH;
			break;
			case Image::CENTER:
				$x = ($this->image->getImageWidth() / 2) - ($watermarkW / 2);
				$y = ($this->image->getImageHeight() / 2) - ($watermarkH / 2);
			break;
			default:
				$x = 0;
				$y = 0;
		}
		
		$this->image->compositeImage($watermark, PHP_Imagick::COMPOSITE_OVER, $x, $y);
		
		$watermark->destroy();
		
		return $this;
	}
	
	/**
	* Converts image to greyscale.
	*
	* @access  public
	* @return  mako\image\Imagick
	*/
	
	public function greyscale()
	{
		$this->image->setImageType(PHP_Imagick::IMGTYPE_GRAYSCALE);
			
		return $this;
	}

	/**
	* Colorize an image.
	*
	* @access  public
	* @param   string              Hex code for the color
	* @return  mako\image\Imagick
	*/

	public function colorize($color)
	{
		$this->image->colorizeImage($color, 1.0);

		return $this;
	}
	
	/**
	* Adds a border to the image.
	*
	* @access  public
	* @param   string             Hex code for the color
	* @param   int                Thickness of the frame in pixels
	* @return  mako\image\Imagick
	*/
	
	public function border($color = '#000', $thickness = 5)
	{
		$this->image->shaveImage($thickness, $thickness);
		
		$this->image->borderImage($color, $thickness, $thickness);
		
		return $this;
	}

	/**
	* Saves image to file and in the specified quality (quality only affects jpg/jpeg and png).
	*
	* @access  public
	* @param   string  Path to the image file
	* @param   int     (optional) Image quality in percent
	*/

	public function save($file, $quality = 85)
	{
		// Check if image save path is writable

		$pathInfo = pathinfo($file);

		if(!is_writable($pathInfo['dirname']))
		{
			throw new RuntimeException(vsprintf("%s(): '%s' is not writable.", array(__METHOD__, $pathInfo['dirname'])));
		}
		
		// Make sure that quality is between 0 and 100
		
		$quality = max(min((int) $quality, 100), 0);
		
		// Save image
		
		$this->image->setImageCompression($quality);
		
		$this->image->writeImage($file);
	}
}

/** -------------------- End of file --------------------**/