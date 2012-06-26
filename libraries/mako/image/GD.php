<?php

namespace mako\image;

use \mako\Image;
use \RuntimeException;
use \InvalidArgumentException;

/**
* Class that manipulates images using GD2.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class GD extends \mako\image\Adapter
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	* Holds info about the image.
	*
	* @var array
	*/

	protected $imageInfo;

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
			if(defined('GD_VERSION') === false || version_compare(GD_VERSION, '2.0.0', '>=') === false)
			{
				throw new RuntimeException(vsprintf("%s(): GD 2.0.0 or higher is required.", array(__METHOD__)));
			}

			$check = true;
		}
		
		// Create image

		$this->imageInfo = $this->imageInfo($file);

		$this->image = $this->createImage($file, $this->imageInfo);
	}

	/**
	* Destructor.
	*
	* @access  public
	*/

	public function __destruct()
	{
		imagedestroy($this->image);
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	* Returns information about the image.
	*
	* @access  protected
	* @param   string     $file  Path to the image file
	* @return  array
	*/

	protected function imageInfo($file)
	{
		$imageInfo = getimagesize($file);
		
		if($imageInfo === false)
		{
			throw new RuntimeException(vsprintf("%s(): Unable to process the image ('%s').", array(__METHOD__, $file)));
		}

		return $imageInfo;
	}
	
	/**
	* Create a new image from file.
	*
	* @access  protected
	* @param   string     $file       Path to the image file
	* @param   array      $imageInfo  Image info
	* @return  resource
	*/
	
	protected function createImage($file, $imageInfo)
	{
		// Create image from file

		switch($imageInfo[2])
		{
			case IMAGETYPE_JPEG:
				return imagecreatefromjpeg($file);
			break;
			case IMAGETYPE_GIF:
				return imagecreatefromgif($file);
			break;
			case IMAGETYPE_PNG:
				return imagecreatefrompng($file);
			break;
			default:
				throw new RuntimeException(vsprintf("%s(): Unable to open '%s'. Unsupported image type.", array(__METHOD__, $pathInfo['extension'])));
		}
	}
	
	/**
	* Creates a color based on a hex value.
	*
	* @access  protected
	* @param   string     $hex        Hex code of the color
	* @param   int        $alpha      Alpha
	* @param   boolean    $returnRGB  FALSE returns a color identifier, TRUE returns a RGB array
	* @return  int
	*/
	
	protected function createColor($hex, $alpha = 100, $returnRGB = false)
	{
		$hex = str_replace('#', '', $hex);
		
		if(preg_match('/^([a-f0-9]{3}){1,2}$/i', $hex) === 0)
		{
			throw new InvalidArgumentException(vsprintf("%s(): Invalid color code ('%s').", array(__METHOD__, $hex)));
		}
		
		if(strlen($hex) === 3)
		{
			$r = hexdec(str_repeat(substr($hex, 0, 1), 2));
			$g = hexdec(str_repeat(substr($hex, 1, 1), 2));
			$b = hexdec(str_repeat(substr($hex, 2, 1), 2));
		}
		else
		{
			$r = hexdec(substr($hex, 0, 2));
			$g = hexdec(substr($hex, 2, 2));
			$b = hexdec(substr($hex, 4, 2));
		}

		if($returnRGB === true)
		{
			return array('r' => $r, 'g' => $g, 'b' => $b);
		}
		else
		{
			// Convert alpha to 0-127
		
			$alpha = min(round(abs(($alpha * 127 / 100) - 127)), 127);
		
			return imagecolorallocatealpha($this->image, $r, $g, $b, $alpha);
		}
	}

	/**
	* Rotates the image using the given angle in degrees.
	*
	* @access  public
	* @param   int            $degrees  Degrees to rotate the image
	* @return  mako\image\GD
	*/

	public function rotate($degrees)
	{
		if(GD_BUNDLED === 0)
		{
			throw new RuntimeException(vsprintf("%s(): This method requires the 'imagerotate' function which is only available in the bundled version of GD.", array(__METHOD__)));
		}
		
		$w = imagesx($this->image);
		$h = imagesy($this->image);

		$transparent = imagecolorallocatealpha($this->image, 0, 0, 0, 127);

		if($this->imageInfo[2] === IMAGETYPE_GIF)
		{
			$temp = imagecreatetruecolor($w, $h);

			imagefill($temp, 0, 0, $transparent);

			imagecopy($temp, $this->image, 0, 0, 0, 0, $w, $h);

			imagedestroy($this->image);

			$this->image = $temp;
		}

		$this->image = imagerotate($this->image, (360 - $degrees), $transparent);

		imagecolortransparent($this->image, $transparent);

		return $this;
	}

	/**
	* Resizes the image to the chosen size. 
	*
	* @param   int            $width        Width of the image
	* @param   int            $height       (optional) Height of the image
	* @param   int            $aspectRatio  (optional) Aspect ratio
	* @return  mako\image\GD
	*/

	public function resize($width, $height = null, $aspectRatio = null)
	{
		$w = imagesx($this->image);
		$h = imagesy($this->image);

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

		$resized = imagecreatetruecolor($newWidth, $newHeight);

		$transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);

		imagefill($resized, 0, 0, $transparent);

		imagecopyresized($resized, $this->image, 0, 0, 0, 0, $newWidth, $newHeight, $w, $h);

		imagedestroy($this->image);

		imagecolortransparent($resized, $transparent);

		$this->image = $resized;

		return $this;
	}

	/**
	* Crops the image.
	*
	* @access  public
	* @param   int            $width   Width of the crop
	* @param   int            $height  Height of the crop
	* @param   int            $x       The X coordinate of the cropped region's top left corner
	* @param   int            $y       The Y coordinate of the cropped region's top left corner
	* @return  mako\image\GD
	*/

	public function crop($width, $height, $x, $y)
	{			
		$w = imagesx($this->image);
		$h = imagesy($this->image);

		$crop = imagecreatetruecolor($width, $height);

		$transparent = imagecolorallocatealpha($crop, 0, 0, 0, 127);

		imagefill($crop, 0, 0, $transparent);

		imagecopy($crop, $this->image, 0, 0, $x, $y, $w, $h);

		imagedestroy($this->image);

		imagecolortransparent($crop, $transparent);

		$this->image = $crop;
		
		return $this;
	}

	/**
	* Flips the image.
	*
	* @access  public  
	* @param   int            $direction  (optional) Direction to flip the image
	* @return  mako\image\GD
	*/

	public function flip($direction = null)
	{
		$w = imagesx($this->image);
		$h = imagesy($this->image);

		$flipped = imagecreatetruecolor($w, $h);

		$transparent = imagecolorallocatealpha($flipped, 0, 0, 0, 127);

		imagefill($flipped, 0, 0, $transparent);

		if($direction ===  Image::VERTICAL)
		{
			// Flips the image in the vertical direction

			for($y = 0; $y < $h; $y++)
			{
				imagecopy($flipped, $this->image, 0, $y, 0, $h - $y - 1, $w, 1);
			}
		}
		else
		{
			// Flips the image in the horizontal direction

			for($x = 0; $x < $w; $x++)
			{
				imagecopy($flipped, $this->image, $x, 0, $w - $x - 1, 0, 1, $h);
			}
		}

		imagedestroy($this->image);

		imagecolortransparent($flipped, $transparent);

		$this->image = $flipped;

		return $this;
	}
	
	/**
	* Adds a watermark to the image.
	*
	* @access  public
	* @param   string         $file      Path to the image file
	* @param   int            $position  (optional) Position of the watermark
	* @param   int            $opacity   (optional) Opacity of the watermark in percent
	* @return  mako\image\GD
	*/
	
	public function watermark($file, $position = null, $opacity = 100)
	{
		// Check if the image exists

		if(file_exists($file) === false)
		{
			throw new RuntimeException(vsprintf("%s(): The image file ('%s') does not exist.", array(__METHOD__, $file)));
		}
		
		$watermark = $this->createImage($file, $this->imageInfo($file));
		
		$watermarkW = imagesx($watermark);
		$watermarkH = imagesy($watermark);
		
		// Make sure that opacity is between 0 and 100
		
		$opacity = max(min((int) $opacity, 100), 0);
			
		if($opacity < 100)
		{
			if(GD_BUNDLED === 0)
			{
				throw new RuntimeException(vsprintf("%s(): Setting watermark opacity requires the 'imagelayereffect' function which is only available in the bundled version of GD.", array(__METHOD__)));
			}
			
			// Convert alpha to 0-127
			
			$alpha = min(round(abs(($opacity * 127 / 100) - 127)), 127);
			
			$transparent = imagecolorallocatealpha($watermark, 0, 0, 0, $alpha);

			imagelayereffect($watermark, IMG_EFFECT_OVERLAY);

			imagefilledrectangle($watermark, 0, 0, $watermarkW, $watermarkH, $transparent);
		}
		
		// Position the watermark.
		
		switch($position)
		{
			case Image::TOP_RIGHT:
				$x = imagesx($this->image) - $watermarkW;
				$y = 0;
			break;
			case Image::BOTTOM_LEFT:
				$x = 0;
				$y = imagesy($this->image) - $watermarkH;
			break;
			case Image::BOTTOM_RIGHT:
				$x = imagesx($this->image) - $watermarkW;
				$y = imagesy($this->image) - $watermarkH;
			break;
			case Image::CENTER:
				$x = (imagesx($this->image) / 2) - ($watermarkW / 2);
				$y = (imagesy($this->image) / 2) - ($watermarkH / 2);
			break;
			default:
				$x = 0;
				$y = 0;
		}
		
		imagealphablending($this->image, true);
					
		imagecopy($this->image, $watermark, $x, $y, 0, 0, $watermarkW, $watermarkH);
		
		imagedestroy($watermark);
		
		return $this;
	}
	
	/**
	* Converts image to greyscale.
	*
	* @access  public
	* @return  mako\image\GD
	*/
	
	public function greyscale()
	{
		if(GD_BUNDLED === 0)
		{
			$w = imagesx($this->image);
			$h = imagesy($this->image);

			$temp = imagecreatetruecolor($w, $h);
			
			// Generate array of shades of grey
			
			$greys = array();

			for($i = 0; $i <= 255; $i++)
			{
			    $greys[$i] = imagecolorallocate($temp, $i, $i, $i);
			}
			
			// Convert pixels to greyscale

			for($x = 0; $x < $w; $x++) 
			{
				for($y = 0; $y < $h; $y++)
				{
					$rgb = imagecolorat($this->image, $x, $y);

					$r = ($rgb >> 16) & 0xFF;
					$g = ($rgb >> 8) & 0xFF;
					$b = $rgb & 0xFF;

					imagesetpixel($temp, $x, $y, $greys[((0.299 * $r) + (0.587 * $g) + (0.114 * $b))]);
				}
			}

			imagedestroy($this->image);

			$this->image = $temp;
		}
		else
		{
			imagefilter($this->image, IMG_FILTER_GRAYSCALE);
		}
					
		return $this;
	}

	/**
	* Colorize an image.
	*
	* @access  public
	* @param   string         $color  Hex code for the color
	* @return  mako\image\GD
	*/

	public function colorize($color)
	{
		if(GD_BUNDLED === 0)
		{
			throw new RuntimeException(vsprintf("%s(): This method requires the 'imagefilter' function which is only available in the bundled version of GD.", array(__METHOD__)));
		}
		
		$color = $this->createColor($color, 0, true);

		imagefilter($this->image, IMG_FILTER_COLORIZE, $color['r'], $color['g'], $color['b'], 0);

		return $this;
	}
	
	/**
	* Adds a border to the image.
	*
	* @access  public
	* @param   string         $color      Hex code for the color
	* @param   int            $thickness  Thickness of the frame in pixels
	* @return  mako\image\GD
	*/
	
	public function border($color = '#000', $thickness = 5)
	{
		$w = imagesx($this->image);
		$h = imagesy($this->image);
		
		$color = $this->createColor($color);
		
		for($i = 0; $i < $thickness; $i++) 
		{
			if($i < 0)
			{
				$x = $w + 1;
				$y = $h + 1;
			}
			else
			{
				$x = --$w;
				$y = --$h;
			}
			
			imagerectangle($this->image, $i, $i, $x, $y, $color); 
		}
		
		return $this;
	}

	/**
	* Saves image to file and in the specified quality (quality only affects jpg/jpeg and png).
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
		
		// Save image

		switch($pathInfo['extension'])
		{
			case 'jpg':
			case 'jpeg':
				imagejpeg($this->image, $file, $quality);
			break;
			case 'gif':
				imagegif($this->image, $file);
			break;
			case 'png':
				imagealphablending($this->image, true);
				imagesavealpha($this->image, true);
				imagepng($this->image, $file, (9 - (round(($quality / 100) * 9))));
			break;
			default:
				throw new RuntimeException(vsprintf("%s(): Unable to save to '%s'. Unsupported image format.", array(__METHOD__, $pathInfo['extension'])));
		}
	}
}

/** -------------------- End of file --------------------**/