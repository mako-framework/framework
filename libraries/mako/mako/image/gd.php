<?php

namespace mako\image
{
	use \mako\Image;
	use \Exception;
	use \InvalidArgumentException;
	
	/**
	* Class that manipulates images using GD2.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class GD
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------

		/**
		* Temporary image resource.
		*/

		protected $image;

		/**
		* Holds info about the image.
		*/

		protected $imageInfo;

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
				if(defined('GD_VERSION') === false || version_compare(GD_VERSION, '2.0.0', '>=') === false)
				{
					throw new Exception(__CLASS__ . ": GD 2.0.0 or higher is required.");
				}

				$check = true;
			}
			
			// Create image

			$this->image = $this->createImage($file);
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
		* Create a new image from file.
		*
		* @access  protected
		* @param   string     Path to the image file
		* @return  resource
		*/
		
		protected function createImage($file)
		{
			// Get image info

			$this->imageInfo = getimagesize($file);
			
			if($this->imageInfo === false)
			{
				throw new Exception(__CLASS__ . ": Unable to process the image ('{$file}').");
			}

			// Create image from file

			switch($this->imageInfo[2])
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
					throw new Exception(__CLASS__ . ": Unable to open '{$pathInfo['extension']}'. Unsupported image type.");
			}
		}
		
		/**
		* Creates a colour based on a hex value.
		*
		* @access  protected
		* @param   string     Hex code of the colour
		* @param   int        Alpha
		* @return  int
		*/
		
		protected function createColour($hex, $alpha = 100)
		{
			$hex = str_replace('#', '', $hex);
			
			if(preg_match('/^([a-f0-9]{3}){1,2}$/i', $hex) === 0)
			{
				throw new InvalidArgumentException(__CLASS__ . ": Invalid colour code.");
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
			
			// Convert alpha to 0-127
			
			$alpha = min(round(abs(($alpha * 127 / 100) - 127)), 127);
			
			return imagecolorallocatealpha($this->image, $r, $g, $b, $alpha);
		}

		/**
		* Rotates the image using the given angle in degrees.
		*
		* @access  public
		* @param   int     Degrees to rotate the image
		* @return  GD
		*/

		public function rotate($degrees)
		{
			if(GD_BUNDLED === 0)
			{
				throw new Exception(__CLASS__ . ": This method requires the 'imagerotate' function which is only available in the bundled version of GD.");
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
		* @param   int      Width of the image
		* @param   int      (optional) Height of the image
		* @param   int      (optional) Aspect ratio
		* @return  GD
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
		* Flips the image.
		*
		* @access  public  
		* @param   int     (optional) Direction to flip the image
		* @return  GD
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
		* @param   string  Path to the image file
		* @param   int     (optional) Position of the watermark
		* @param   int     (optional) Opacity of the watermark in percent
		* @return  GD
		*/
		
		public function watermark($file, $position = null, $opacity = 100)
		{
			// Check if the image exists

			if(file_exists($file) === false)
			{
				throw new Exception(__CLASS__ . ": Image file ('{$file}') does not exist.");
			}
			
			$watermark = $this->createImage($file);
			
			$watermarkW = imagesx($watermark);
			$watermarkH = imagesy($watermark);
			
			// Make sure that opacity is between 0 and 100
			
			$opacity = max(min((int) $opacity, 100), 0);
				
			if($opacity < 100)
			{
				if(GD_BUNDLED === 0)
				{
					throw new Exception(__CLASS__ . ": Setting watermak opacity requires the 'imagelayereffect' function which is only available in the bundled version of GD.");
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
		* @return  GD
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
		* Adds a border to the image.
		*
		* @access  public
		* @param   string  Hex code for the colour
		* @param   int     Thickness of the frame in pixels
		* @return  GD
		*/
		
		public function border($colour = '#000', $thickness = 5)
		{
			$w = imagesx($this->image);
			$h = imagesy($this->image);
			
			$colour = static::createColour($colour);
			
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
				
				imagerectangle($this->image, $i, $i, $x, $y, $colour); 
			}
			
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
				throw new Exception(__CLASS__ . ": '{$pathInfo['dirname']}' is not writable.");
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
					throw new Exception(__CLASS__ . ": Unable to save to '{$pathInfo['extension']}'. Unsupported image type.");
			}
		}
	}
}

/** -------------------- End of file --------------------**/