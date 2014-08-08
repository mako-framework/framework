<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\pixl\processors;

use \InvalidArgumentException;
use \RuntimeException;

use \mako\pixl\Image;

/**
 * GD processor.
 *
 * @author  Frederic G. Østby
 */

class GD implements \mako\pixl\processors\ProcessorInterface
{
	/**
	 * Image resource.
	 * 
	 * @var resource
	 */

	protected $image;

	/**
	 * Image resource.
	 * 
	 * @var resource
	 */

	protected $snapshot;

	/**
	 * Image info.
	 * 
	 * @var array
	 */

	protected $imageInfo;

	/**
	 * Do we have the imagefilter function?
	 * 
	 * @var boolean
	 */

	protected $hasFilters;

	/**
	 * Constructor.
	 * 
	 * @access  public
	 */

	public function __construct()
	{
		$this->hasFilters = function_exists('imagefilter');
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

		if(is_resource($this->snapshot))
		{
			imagedestroy($this->snapshot);
		}
	}

	/**
	 * Collects information about the image.
	 *
	 * @access  protected
	 * @param   string     $file  Path to image file
	 * @return  resource
	 */

	protected function getImageInfo($file)
	{
		$imageInfo = getimagesize($file);
		
		if($imageInfo === false)
		{
			throw new RuntimeException(vsprintf("%s(): Unable to process the image [ %s ].", [__METHOD__, $file]));
		}

		return $imageInfo;
	}

	/**
	 * Creates an image resource that we can work with.
	 * 
	 * @access  protected
	 * @param   string     $image      Path to image file
	 * @param   array      $imageInfo  Image info
	 * @return  resource
	 */

	protected function createImageResource($image, $imageInfo)
	{
		switch($imageInfo[2])
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

	protected function hexToRgb($hex)
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
	 * Opens the image we want to work with.
	 * 
	 * @access  public
	 * @param   string  $image  Path to image file
	 */

	public function open($image)
	{
		$this->imageInfo = $this->getImageInfo($image);

		$this->image = $this->createImageResource($image, $this->imageInfo);
	}

	/**
	 * Creates a snapshot of the image resource.
	 * 
	 * @access  public
	 */

	public function snapshot()
	{
		$w = imagesx($this->image);
		$h = imagesy($this->image);

		$this->snapshot = imagecreatetruecolor($w, $h);

		imagecopy($this->snapshot, $this->image, 0, 0, 0, 0, $w, $h);
	}

	/**
	 * Restores an image snapshot.
	 * 
	 * @access  public
	 */

	public function restore()
	{
		if(!is_resource($this->snapshot))
		{
			throw new RuntimeException(vsprintf("%s(): No snapshot to restore.", [__METHOD__]));
		}

		$this->image = $this->snapshot;

		$this->snapshot = null;
	}

	/**
	 * Rotates the image using the given angle in degrees.
	 *
	 * @access  public
	 * @param   int     $degrees  Degrees to rotate the image
	 */

	public function rotate($degrees)
	{	
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
			elseif($aspectRatio === Image::RESIZE_WIDTH)
			{
				// Base new size on given width while maintaining aspect ratio

				$newWidth  = $width;
				$newHeight = round($h * ($width / $w));
			}
			elseif($aspectRatio === Image::RESIZE_HEIGHT)
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
		$watermark = $this->createImageResource($file, $this->getImageInfo($file));
		
		$watermarkW = imagesx($watermark);
		$watermarkH = imagesy($watermark);
			
		if($opacity < 100)
		{	
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
	 * Adjust image brightness.
	 * 
	 * @access  public
	 * @param   int     $level  (optional) Brightness level (-100 to 100)
	 */

	public function brightness($level = 50)
	{
		$level *= 2.5;

		if($this->hasFilters)
		{
			imagefilter($this->image, IMG_FILTER_BRIGHTNESS, $level);
		}
		else
		{
			$w = imagesx($this->image);
			$h = imagesy($this->image);

			$temp = imagecreatetruecolor($w, $h);
			
			// Adjust pixel brightness

			for($x = 0; $x < $w; $x++) 
			{
				for($y = 0; $y < $h; $y++)
				{
					$rgb = imagecolorat($this->image, $x, $y);

					$r = (($rgb >> 16) & 0xFF) + $level;
					$g = (($rgb >> 8) & 0xFF ) + $level;
					$b = ($rgb & 0xFF) + $level;

					$r = ($r > 255) ? 255 : (($r < 0) ? 0 : $r);
					$g = ($g > 255) ? 255 : (($g < 0) ? 0 : $g);
					$b = ($b > 255) ? 255 : (($b < 0) ? 0 : $b);

					imagesetpixel($temp, $x, $y, imagecolorallocate($temp, $r, $g, $b));
				}
			}

			imagedestroy($this->image);

			$this->image = $temp;
		}
	}

	/**
	 * Converts image to greyscale.
	 *
	 * @access  public
	 */
	
	public function greyscale()
	{
		if($this->hasFilters)
		{
			imagefilter($this->image, IMG_FILTER_GRAYSCALE);
		}
		else
		{
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
	}

	/**
	 * Converts image to sepia.
	 *
	 * @access  public
	 */

	public function sepia()
	{
		$w = imagesx($this->image);
		$h = imagesy($this->image);

		$temp = imagecreatetruecolor($w, $h);
		
		// Convert pixels to sepia

		for($x = 0; $x < $w; $x++) 
		{
			for($y = 0; $y < $h; $y++)
			{
				$rgb = imagecolorat($this->image, $x, $y);

				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;

				$newR = ($r * 0.393 + $g * 0.769 + $b * 0.189) * 0.85;
				$newG = ($r * 0.349 + $g * 0.686 + $b * 0.168) * 0.85;
				$newB = ($r * 0.272 + $g * 0.534 + $b * 0.131) * 0.85;

				$newR = ($newR > 255) ? 255 : (($newR < 0) ? 0 : $newR);
				$newG = ($newG > 255) ? 255 : (($newG < 0) ? 0 : $newG);
				$newB = ($newB > 255) ? 255 : (($newB < 0) ? 0 : $newB);

				imagesetpixel($temp, $x, $y, imagecolorallocate($temp, $newR, $newG, $newB));
			}
		}

		imagedestroy($this->image);

		$this->image = $temp;
	}

	/**
	 * Colorize the image.
	 *
	 * @access  public
	 * @param   string  $color  HEX value
	 */

	public function colorize($color)
	{	
		$rgb = $this->hexToRgb($color);

		if($this->hasFilters)
		{
			imagefilter($this->image, IMG_FILTER_COLORIZE, $rgb['r'], $rgb['g'], $rgb['b'], 0);
		}
		else
		{
			$colorize = $rgb;

			$w = imagesx($this->image);
			$h = imagesy($this->image);

			$temp = imagecreatetruecolor($w, $h);
			
			// Colorize pixels

			for($x = 0; $x < $w; $x++) 
			{
				for($y = 0; $y < $h; $y++)
				{
					$rgb = imagecolorat($this->image, $x, $y);

					$r = (($rgb >> 16) & 0xFF) + $colorize['r'];
					$g = (($rgb >> 8) & 0xFF ) + $colorize['g'];
					$b = ($rgb & 0xFF) + $colorize['b'];

					$r = ($r > 255) ? 255 : (($r < 0) ? 0 : $r);
					$g = ($g > 255) ? 255 : (($g < 0) ? 0 : $g);
					$b = ($b > 255) ? 255 : (($b < 0) ? 0 : $b);

					imagesetpixel($temp, $x, $y, imagecolorallocate($temp, $r, $g, $b));
				}
			}

			imagedestroy($this->image);

			$this->image = $temp;
		}
	}

	/**
	 * Sharpens the image.
	 * 
	 * @access  public
	 */

	public function sharpen()
	{
		$sharpen = [[-1.2, -1, -1.2], [-1, 20, -1], [-1.2, -1, -1.2]];

		$divisor = array_sum(array_map('array_sum', $sharpen));

		imageconvolution($this->image, $sharpen, $divisor, 0);
	}

	/**
	 * Pixelates the image.
	 * 
	 * @access  public
	 * @param   int     $pixelSize  (optional) Pixel size
	 */

	public function pixelate($pixelSize = 10)
	{
		if($this->hasFilters)
		{
			imagefilter($this->image, IMG_FILTER_PIXELATE, $pixelSize, IMG_FILTER_PIXELATE);
		}
		else
		{
			$width = imagesx($this->image);

			$height = imagesy($this->image);

			$this->resize((int) ($width / $pixelSize), (int) ($height / $pixelSize));

			$this->resize($width, $height);
		}	
	}

	/**
	 * Negates the image.
	 * 
	 * @access  public
	 */

	public function negate()
	{
		if($this->hasFilters)
		{
			imagefilter($this->image, IMG_FILTER_NEGATE);
		}
		else
		{
			$w = imagesx($this->image);
			$h = imagesy($this->image);

			$temp = imagecreatetruecolor($w, $h);
			
			// Invert pixel colors

			for($x = 0; $x < $w; $x++) 
			{
				for($y = 0; $y < $h; $y++)
				{
					imagesetpixel($temp, $x, $y, imagecolorat($this->image, $x, $y) ^ 0x00FFFFFF);
				}
			}

			imagedestroy($this->image);

			$this->image = $temp;
		}
	}

	/**
	 * Adds a border to the image.
	 *
	 * @access  public
	 * @param   string  $color      (optional) Hex value
	 * @param   int     $thickness  (optional) Thickness of the border in pixels
	 */
	
	public function border($color = '#000', $thickness = 5)
	{
		$w = imagesx($this->image);
		$h = imagesy($this->image);
		
		$rgb = $this->hexToRgb($color);

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