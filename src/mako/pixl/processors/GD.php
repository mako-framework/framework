<?php

namespace mako\pixl\processors;

use \InvalidArgumentException;
use \RuntimeException;

use \mako\pixl\Image;

/**
 * GD processor.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2014 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class GD implements \mako\pixl\processors\ProcessorInterface
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Image resource.
	 * 
	 * @var string
	 */

	protected $image;

	/**
	 * Image info.
	 * 
	 * @var array
	 */

	protected $imageInfo;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 *
	 * @access  public
	 */

	public function __construct()
	{
		// Nothing here
	}

	/**
	 * Destructor.
	 *
	 * @access  public
	 */

	public function __destruct()
	{
		if(is_resource($this->image))
		{
			imagedestroy($this->image);
		}	
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Collects information about the image.
	 *
	 * @access  protected
	 * @param   string     $file  Path to image file
	 */

	protected function collectImageInfo($file)
	{
		$this->imageInfo = getimagesize($file);
		
		if($this->imageInfo === false)
		{
			throw new RuntimeException(vsprintf("%s(): Unable to process the image [ %s ].", [__METHOD__, $file]));
		}
	}

	/**
	 * Creates an image resource that we can work with.
	 * 
	 * @access  protected
	 * @param   string     $image  Path to image file
	 * @return  resource
	 */

	protected function createImageResource($image)
	{
		switch($this->imageInfo[2])
		{
			case IMAGETYPE_JPEG:
				return imagecreatefromjpeg($image);
				break;
			case IMAGETYPE_GIF:
				return imagecreatefromgif($image);
				break;
			case IMAGETYPE_PNG:
				return imagecreatefrompng($image);
				break;
			default:
				throw new RuntimeException(vsprintf("%s(): Unable to open [ %s ]. Unsupported image type.", [__METHOD__, pathinfo($file, PATHINFO_EXTENSION)]));
		}
	}

	/**
	 * Converts HEX value to an RGB array.
	 * 
	 * @access  protected
	 * @param   string     $hex  HEX value
	 * @return  array
	 */

	protected function HEX2RGB($hex)
	{
		$hex = str_replace('#', '', $hex);
		
		if(preg_match('/^([a-f0-9]{3}){1,2}$/i', $hex) === 0)
		{
			throw new InvalidArgumentException(vsprintf("%s(): Invalid HEX value [ %s ].", [__METHOD__, $hex]));
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

		return ['r' => $r, 'g' => $g, 'b' => $b];
	}

	/**
	 * Sets the image we want to work with.
	 * 
	 * @access  public
	 * @param   string  $image  Path to image file
	 */

	public function setImage($image)
	{
		$this->collectImageInfo($image);

		$this->image = $this->createImageResource($image);
	}

	/**
	 * Rotates the image using the given angle in degrees.
	 *
	 * @access  public
	 * @param   int     $degrees  Degrees to rotate the image
	 */

	public function rotate($degrees)
	{
		if(GD_BUNDLED === 0)
		{
			throw new RuntimeException(vsprintf("%s(): This method requires the [ imagerotate ] function which is only available in the bundled version of GD.", [__METHOD__]));
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
	}

	/**
	 * Resizes the image to the chosen size. 
	 *
	 * @access  public
	 * @param   int     $width        Width of the image
	 * @param   int     $height       (optional) Height of the image
	 * @param   int     $aspectRatio  (optional) Aspect ratio
	 */

	public function resize($width, $height = null, $aspectRatio = Image::RESIZE_IGNORE)
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
			if($aspectRatio === Image::RESIZE_AUTO)
			{
				// Calculate smallest size based on given height and width while maintaining aspect ratio

				$percentage = min(($width / $w), ($height / $h));

				$newWidth  = round($w * $percentage);
				$newHeight = round($h * $percentage);
			}
			else if($aspectRatio === Image::RESIZE_WIDTH)
			{
				// Base new size on given width while maintaining aspect ratio

				$newWidth  = $width;
				$newHeight = round($h * ($width / $w));
			}
			else if($aspectRatio === Image::RESIZE_HEIGHT)
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
	}

	/**
	 * Crops the image.
	 *
	 * @access  public
	 * @param   int     $width   Width of the crop
	 * @param   int     $height  Height of the crop
	 * @param   int     $x       The X coordinate of the cropped region's top left corner
	 * @param   int     $y       The Y coordinate of the cropped region's top left corner
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
	}

	/**
	 * Flips the image.
	 *
	 * @access  public
	 * @param   int     $direction  (optional) Direction to flip the image
	 */

	public function flip($direction = Image::FLIP_HORIZONTAL)
	{
		$w = imagesx($this->image);
		$h = imagesy($this->image);

		$flipped = imagecreatetruecolor($w, $h);

		$transparent = imagecolorallocatealpha($flipped, 0, 0, 0, 127);

		imagefill($flipped, 0, 0, $transparent);

		if($direction ===  Image::FLIP_VERTICAL)
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
	}

	/**
	 * Adds a watermark to the image.
	 *
	 * @access  public
	 * @param   string  $file      Path to the image file
	 * @param   int     $position  (optional) Position of the watermark
	 * @param   int     $opacity   (optional) Opacity of the watermark in percent
	 */
	
	public function watermark($file, $position = Image::WATERMARK_TOP_LEFT, $opacity = 100)
	{
		// Check if the image exists

		if(file_exists($file) === false)
		{
			throw new RuntimeException(vsprintf("%s(): The watermark image [ %s ] does not exist.", [__METHOD__, $file]));
		}
		
		$watermark = $this->createImage($file, $this->imageInfo($file));
		
		$watermarkW = imagesx($watermark);
		$watermarkH = imagesy($watermark);
			
		if($opacity < 100)
		{
			if(GD_BUNDLED === 0)
			{
				throw new RuntimeException(vsprintf("%s(): Setting watermark opacity requires the [ imagelayereffect ] function which is only available in the bundled version of GD.", [__METHOD__]));
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
			case Image::WATERMARK_TOP_RIGHT:
				$x = imagesx($this->image) - $watermarkW;
				$y = 0;
				break;
			case Image::WATERMARK_BOTTOM_LEFT:
				$x = 0;
				$y = imagesy($this->image) - $watermarkH;
				break;
			case Image::WATERMARK_BOTTOM_RIGHT:
				$x = imagesx($this->image) - $watermarkW;
				$y = imagesy($this->image) - $watermarkH;
				break;
			case Image::WATERMARK_CENTER:
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
	}

	/**
	 * Converts image to greyscale.
	 *
	 * @access  public
	 */
	
	public function greyscale()
	{
		if(GD_BUNDLED === 0)
		{
			// GD isnt bundled so we'll have to do it the hard way

			$w = imagesx($this->image);
			$h = imagesy($this->image);

			$temp = imagecreatetruecolor($w, $h);
			
			// Generate array of shades of grey
			
			$greys = [];

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
			// GD is bundled so we can just use an image filter

			imagefilter($this->image, IMG_FILTER_GRAYSCALE);
		}		
	}

	/**
	 * Colorize an image.
	 *
	 * @access  public
	 * @param   string  $color  HEX value
	 */

	public function colorize($color)
	{
		if(GD_BUNDLED === 0)
		{
			throw new RuntimeException(vsprintf("%s(): This method requires the [ imagefilter ] function which is only available in the bundled version of GD.", [__METHOD__]));
		}
		
		$rgb = $this->HEX2RGB($color);

		imagefilter($this->image, IMG_FILTER_COLORIZE, $rgb['r'], $rgb['g'], $rgb['b'], 0);
	}

	/**
	 * Adds a border to the image.
	 *
	 * @access  public
	 * @param   string  $color      Hex value
	 * @param   int     $thickness  Thickness of the border in pixels
	 */
	
	public function border($color = '#000', $thickness = 5)
	{
		$w = imagesx($this->image);
		$h = imagesy($this->image);
		
		$rgb = $this->HEX2RGB($color);

		$color = imagecolorallocatealpha($this->image, $rgb['r'], $rgb['g'], $rgb['b'], 0);
		
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
		$type = $type ?: $this->imageInfo['mime'];

		// Return image blob

		ob_start();

		switch($type)
		{
			case 'gif':
			case 'image/gif':
				imagegif($this->image, null, $quality);
				break;
			case 'jpg':
			case 'jpeg':
			case 'image/jpeg':
				imagejpeg($this->image, null, $quality);
				break;
			case 'png':
			case 'image/png':
				imagealphablending($this->image, true);
				imagesavealpha($this->image, true);
				imagepng($this->image, null, (9 - (round(($quality / 100) * 9))));
				break;
			default:
				throw new RuntimeException(vsprintf("%s(): Unsupported image type [ %s ].", [__METHOD__, $type]));
		}

		return ob_get_clean();

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
		// Get the file extension

		$extension = pathinfo($file, PATHINFO_EXTENSION);
		
		// Save image

		switch($extension)
		{
			case 'gif':
				imagegif($this->image, $file);
				break;
			case 'jpg':
			case 'jpeg':
				imagejpeg($this->image, $file, $quality);
				break;
			case 'png':
				imagealphablending($this->image, true);
				imagesavealpha($this->image, true);
				imagepng($this->image, $file, (9 - (round(($quality / 100) * 9))));
				break;
			default:
				throw new RuntimeException(vsprintf("%s(): Unable to save as [ %s ]. Unsupported image format.", [__METHOD__, $extension]));
		}
	}
}

/** -------------------- End of file -------------------- **/