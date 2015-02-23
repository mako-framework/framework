<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\pixl\processors;

use InvalidArgumentException;
use RuntimeException;

use mako\pixl\Image;
use mako\pixl\processors\CalculateNewDimensionsTrait;
use mako\pixl\processors\ProcessorInterface;

/**
 * GD processor.
 *
 * @author  Frederic G. Østby
 */

class GD implements ProcessorInterface
{
	use CalculateNewDimensionsTrait;

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
	 * {@inheritdoc}
	 */

	public function open($image)
	{
		$this->imageInfo = $this->getImageInfo($image);

		$this->image = $this->createImageResource($image, $this->imageInfo);
	}

	/**
	 * {@inheritdoc}
	 */

	public function snapshot()
	{
		$width  = imagesx($this->image);
		$height = imagesy($this->image);

		$this->snapshot = imagecreatetruecolor($width, $height);

		imagecopy($this->snapshot, $this->image, 0, 0, 0, 0, $width, $height);
	}

	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */

	public function getWidth()
	{
		return imagesx($this->image);
	}

	/**
	 * {@inheritdoc}
	 */

	public function getHeight()
	{
		return imagesy($this->image);
	}

	/**
	 * {@inheritdoc}
	 */

	public function getDimensions()
	{
		return ['width' => $this->getWidth(), 'height' => $this->getHeight()];
	}

	/**
	 * {@inheritdoc}
	 */

	public function rotate($degrees)
	{
		$width  = imagesx($this->image);
		$height = imagesy($this->image);

		$transparent = imagecolorallocatealpha($this->image, 0, 0, 0, 127);

		if($this->imageInfo[2] === IMAGETYPE_GIF)
		{
			$temp = imagecreatetruecolor($width, $height);

			imagefill($temp, 0, 0, $transparent);

			imagecopy($temp, $this->image, 0, 0, 0, 0, $width, $height);

			imagedestroy($this->image);

			$this->image = $temp;
		}

		$this->image = imagerotate($this->image, (360 - $degrees), $transparent);

		imagecolortransparent($this->image, $transparent);
	}

	/**
	 * {@inheritdoc}
	 */

	public function resize($width, $height = null, $aspectRatio = Image::RESIZE_IGNORE)
	{
		$oldWidth  = imagesx($this->image);
		$oldHeight = imagesy($this->image);

		list($newWidth, $newHeight) = $this->calculateNewDimensions($width, $height, $oldWidth, $oldHeight, $aspectRatio);

		$resized = imagecreatetruecolor($newWidth, $newHeight);

		$transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);

		imagefill($resized, 0, 0, $transparent);

		imagecopyresized($resized, $this->image, 0, 0, 0, 0, $newWidth, $newHeight, $oldWidth, $oldHeight);

		imagedestroy($this->image);

		imagecolortransparent($resized, $transparent);

		$this->image = $resized;
	}

	/**
	 * {@inheritdoc}
	 */

	public function crop($width, $height, $x, $y)
	{
		$oldWidth  = imagesx($this->image);
		$oldHeight = imagesy($this->image);

		$crop = imagecreatetruecolor($width, $height);

		$transparent = imagecolorallocatealpha($crop, 0, 0, 0, 127);

		imagefill($crop, 0, 0, $transparent);

		imagecopy($crop, $this->image, 0, 0, $x, $y, $oldWidth, $oldHeight);

		imagedestroy($this->image);

		imagecolortransparent($crop, $transparent);

		$this->image = $crop;
	}

	/**
	 * {@inheritdoc}
	 */

	public function flip($direction = Image::FLIP_HORIZONTAL)
	{
		$width  = imagesx($this->image);
		$height = imagesy($this->image);

		$flipped = imagecreatetruecolor($width, $height);

		$transparent = imagecolorallocatealpha($flipped, 0, 0, 0, 127);

		imagefill($flipped, 0, 0, $transparent);

		if($direction ===  Image::FLIP_VERTICAL)
		{
			// Flips the image in the vertical direction

			for($y = 0; $y < $height; $y++)
			{
				imagecopy($flipped, $this->image, 0, $y, 0, $height - $y - 1, $width, 1);
			}
		}
		else
		{
			// Flips the image in the horizontal direction

			for($x = 0; $x < $width; $x++)
			{
				imagecopy($flipped, $this->image, $x, 0, $width - $x - 1, 0, 1, $height);
			}
		}

		imagedestroy($this->image);

		imagecolortransparent($flipped, $transparent);

		$this->image = $flipped;
	}

	/**
	 * {@inheritdoc}
	 */

	public function watermark($file, $position = Image::WATERMARK_TOP_LEFT, $opacity = 100)
	{
		$watermark = $this->createImageResource($file, $this->getImageInfo($file));

		$watermarkWidth  = imagesx($watermark);
		$watermarkHeight = imagesy($watermark);

		if($opacity < 100)
		{
			// Convert alpha to 0-127

			$alpha = min(round(abs(($opacity * 127 / 100) - 127)), 127);

			$transparent = imagecolorallocatealpha($watermark, 0, 0, 0, $alpha);

			imagelayereffect($watermark, IMG_EFFECT_OVERLAY);

			imagefilledrectangle($watermark, 0, 0, $watermarkWidth, $watermarkHeight, $transparent);
		}

		// Position the watermark.

		switch($position)
		{
			case Image::WATERMARK_TOP_RIGHT:
				$x = imagesx($this->image) - $watermarkWidth;
				$y = 0;
				break;
			case Image::WATERMARK_BOTTOM_LEFT:
				$x = 0;
				$y = imagesy($this->image) - $watermarkHeight;
				break;
			case Image::WATERMARK_BOTTOM_RIGHT:
				$x = imagesx($this->image) - $watermarkWidth;
				$y = imagesy($this->image) - $watermarkHeight;
				break;
			case Image::WATERMARK_CENTER:
				$x = (imagesx($this->image) / 2) - ($watermarkWidth / 2);
				$y = (imagesy($this->image) / 2) - ($watermarkHeight / 2);
				break;
			default:
				$x = 0;
				$y = 0;
		}

		imagealphablending($this->image, true);

		imagecopy($this->image, $watermark, $x, $y, 0, 0, $watermarkWidth, $watermarkHeight);

		imagedestroy($watermark);
	}

	/**
	 * {@inheritdoc}
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
			$width  = imagesx($this->image);
			$height = imagesy($this->image);

			$temp = imagecreatetruecolor($width, $height);

			// Adjust pixel brightness

			for($x = 0; $x < $width; $x++)
			{
				for($y = 0; $y < $height; $y++)
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
	 * {@inheritdoc}
	 */

	public function greyscale()
	{
		if($this->hasFilters)
		{
			imagefilter($this->image, IMG_FILTER_GRAYSCALE);
		}
		else
		{
			$width  = imagesx($this->image);
			$height = imagesy($this->image);

			$temp = imagecreatetruecolor($width, $height);

			// Generate array of shades of grey

			$greys = [];

			for($i = 0; $i <= 255; $i++)
			{
			    $greys[$i] = imagecolorallocate($temp, $i, $i, $i);
			}

			// Convert pixels to greyscale

			for($x = 0; $x < $width; $x++)
			{
				for($y = 0; $y < $height; $y++)
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
	 * {@inheritdoc}
	 */

	public function sepia()
	{
		$width  = imagesx($this->image);
		$height = imagesy($this->image);

		$temp = imagecreatetruecolor($width, $height);

		// Convert pixels to sepia

		for($x = 0; $x < $width; $x++)
		{
			for($y = 0; $y < $height; $y++)
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
	 * {@inheritdoc}
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

			$width  = imagesx($this->image);
			$height = imagesy($this->image);

			$temp = imagecreatetruecolor($width, $height);

			// Colorize pixels

			for($x = 0; $x < $width; $x++)
			{
				for($y = 0; $y < $height; $y++)
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
	 * {@inheritdoc}
	 */

	public function sharpen()
	{
		$sharpen = [[-1.2, -1, -1.2], [-1, 20, -1], [-1.2, -1, -1.2]];

		$divisor = array_sum(array_map('array_sum', $sharpen));

		imageconvolution($this->image, $sharpen, $divisor, 0);
	}

	/**
	 * {@inheritdoc}
	 */

	public function pixelate($pixelSize = 10)
	{
		if($this->hasFilters)
		{
			imagefilter($this->image, IMG_FILTER_PIXELATE, $pixelSize, IMG_FILTER_PIXELATE);
		}
		else
		{
			$width  = imagesx($this->image);
			$height = imagesy($this->image);

			$this->resize((int) ($width / $pixelSize), (int) ($height / $pixelSize));

			$this->resize($width, $height);
		}
	}

	/**
	 * {@inheritdoc}
	 */

	public function negate()
	{
		if($this->hasFilters)
		{
			imagefilter($this->image, IMG_FILTER_NEGATE);
		}
		else
		{
			$width  = imagesx($this->image);
			$height = imagesy($this->image);

			$temp = imagecreatetruecolor($width, $height);

			// Invert pixel colors

			for($x = 0; $x < $width; $x++)
			{
				for($y = 0; $y < $height; $y++)
				{
					imagesetpixel($temp, $x, $y, imagecolorat($this->image, $x, $y) ^ 0x00FFFFFF);
				}
			}

			imagedestroy($this->image);

			$this->image = $temp;
		}
	}

	/**
	 * {@inheritdoc}
	 */

	public function border($color = '#000', $thickness = 5)
	{
		$width  = imagesx($this->image);
		$height = imagesy($this->image);

		$rgb = $this->hexToRgb($color);

		$color = imagecolorallocatealpha($this->image, $rgb['r'], $rgb['g'], $rgb['b'], 0);

		for($i = 0; $i < $thickness; $i++)
		{
			if($i < 0)
			{
				$x = $width + 1;
				$y = $height + 1;
			}
			else
			{
				$x = --$width;
				$y = --$height;
			}

			imagerectangle($this->image, $i, $i, $x, $y, $color);
		}
	}

	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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