<?php

namespace mako\image;

use \mako\Image;
use \RuntimeException;

/**
* Class that manipulates images using ImageMagick.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class ImageMagick extends \mako\image\Adapter
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	* Holds the all the options passed to ImageMagick.
	*
	* @var string
	*/

	protected $cmd = '';

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//------------------------------------------------

	/**
	* Constructor.
	*
	* @access  public
	* @param   string  $file  Path to the image file
	*/

	public function __construct($file)
	{
		static $check = false;
		
		// Check if all the requirements are met
		
		if($check === false)
		{	
			if(function_exists('exec') === false)
			{
				throw new RuntimeException(vsprintf("%s(): The 'exec' function has been disabled.", array(__METHOD__)));
			}

			$check = true;
		}
		
		$this->image = escapeshellarg($file);
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	* Rotates the image using the given angle in degrees.
	*
	* @access  public
	* @param   int                     $degrees  Degrees to rotate the image
	* @return  mako\image\ImageMagick
	*/

	public function rotate($degrees)
	{		
		$this->cmd .= '-rotate ' . escapeshellarg((360 - $degrees)) . ' ';

		return $this;
	}

	/**
	* Resizes the image to the chosen size. 
	*
	* @param   int                     $width        Width of the image
	* @param   int                     $height       (optional) Height of the image
	* @param   int                     $aspectRatio  (optional) Aspect ratio
	* @return  mako\image\ImageMagick
	*/

	public function resize($width, $height = null, $aspectRatio = null)
	{
		if($height === null)
		{				
			$this->cmd .= '-resize ' . escapeshellarg((int) $width . '%') . ' ';
		}
		else
		{
			if($aspectRatio === Image::AUTO)
			{
				// Calculate smallest size based on given height and width while maintaining aspect ratio

				$this->cmd .= '-resize ' . escapeshellarg((int) $width . 'x' . (int) $height) . ' ';
			}
			else if($aspectRatio === Image::WIDTH)
			{
				// Base new size on given width while maintaining aspect ratio

				$this->cmd .= '-resize ' . escapeshellarg((int) $width) . ' ';
			}
			else if($aspectRatio === Image::HEIGHT)
			{
				// Base new size on given height while maintaining aspect ratio

				$this->cmd .= '-resize ' . escapeshellarg('x' . (int) $height) . ' ';
			}
			else
			{
				// Ignone aspect ratio
				
				$this->cmd .= '-resize ' . escapeshellarg((int) $width . 'x' . (int) $height . '!') . ' ';
			}						
		}

		return $this;
	}

	/**
	* Crops the image.
	*
	* @access  public
	* @param   int                     $width   Width of the crop
	* @param   int                     $height  Height of the crop
	* @param   int                     $x       The X coordinate of the cropped region's top left corner
	* @param   int                     $y       The Y coordinate of the cropped region's top left corner
	* @return  mako\image\ImageMagick
	*/

	public function crop($width, $height, $x, $y)
	{			
		$this->cmd .= '-crop ' . escapeshellarg((int) $width . 'x' . (int) $height . '+' . (int) $x . '+' . (int) $y) . ' ';
		
		return $this;
	}

	/**
	* Flips the image.
	*
	* @access  public
	* @param   string                  $direction  (optional) Direction to flip the image
	* @return  mako\image\ImageMagick
	*/

	public function flip($direction = null)
	{
		if($direction ===  Image::VERTICAL)
		{
			// Flips the image in the vertical direction

			$this->cmd .= '-flip ';
		}
		else
		{
			// Flips the image in the horizontal direction

			$this->cmd .= '-flop ';
		}

		return $this;
	}
	
	/**
	* Adds a watermark to the image.
	*
	* @access  public
	* @param   string                  $file      Path to the image file
	* @param   int                     $position  (optional) Position of the watermark
	* @param   int                     $opacity   (optional) Opacity of the watermark in percent
	* @return  mako\image\ImageMagick
	*/
	
	public function watermark($file, $position = null, $opacity = 100)
	{
		// Check if the image exists
		
		if(file_exists($file) === false)
		{
			throw new RuntimeException(vsprintf("%s(): The image file ('%s') does not exist.", array(__METHOD__, $file)));
		}
		
		// Make sure that opacity is between 0 and 100
		
		$opacity = max(min((int) $opacity, 100), 0);
		
		// Position the watermark.
		
		switch($position)
		{
			case Image::TOP_RIGHT:
				$pos = 'NorthEast';
			break;
			case Image::BOTTOM_LEFT:
				$pos = 'SouthWest';
			break;
			case Image::BOTTOM_RIGHT:
				$pos = 'SouthEast';
			break;
			case Image::CENTER:
				$pos = 'Center';
			break;
			default:
				$pos = 'NorthWest';
		}
		
		$this->cmd .= '- | composite -dissolve ' . escapeshellarg($opacity) . '% -gravity ' . escapeshellarg($pos) . ' ' . escapeshellarg($file) . ' - - | convert - ';
		
		return $this;
	}
	
	/**
	* Converts image to greyscale.
	*
	* @access  public
	* @return  mako\image\ImageMagick
	*/
	
	public function greyscale()
	{
		$this->cmd .= '-fx \'(r+g+b)/3\' ';
		
		return $this;
	}

	/**
	* Colorize an image.
	*
	* @access  public
	* @param   string              $color  Hex code for the color
	* @return  mako\image\Imagick
	*/

	public function colorize($color)
	{
		$this->cmd .= '-fill ' . escapeshellarg($color) . ' -colorize 50% ';

		return $this;
	}
	
	/**
	* Adds a border to the image.
	*
	* @access  public
	* @param   string                  $color      Hex code for the color
	* @param   int                     $thickness  Thickness of the frame in pixels
	* @return  mako\image\ImageMagick
	*/
	
	public function border($color = '#000', $thickness = 5)
	{
		$this->cmd .= '-shave ' . escapeshellarg($thickness . 'x' . $thickness) . ' -bordercolor ' . escapeshellarg($color) . ' -border ' . escapeshellarg($thickness) . ' ';
		
		return $this;
	}
	
	/**
	* Saves image to file and in the specified quality.
	*
	* @access  public
	* @param   string  $file     Path to the image file
	* @param   int     $quality  (optional) Image quality in percent
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

		// Manipulate and save image

		$file      = escapeshellarg($file);
		$quality   = escapeshellarg((int) $quality.'%');
		$this->cmd = trim($this->cmd);

		exec("convert {$this->image} {$this->cmd} -quality {$quality} {$file}", $output, $code);

		if($code !== 0)
		{
			$error = ($code === 127) ? 'ImageMagick could not be found.' : 'An error occured.';

			throw new RuntimeException(vsprintf("%s(): %s", array(__METHOD__, $error)));
		}
	}
}

/** -------------------- End of file --------------------**/