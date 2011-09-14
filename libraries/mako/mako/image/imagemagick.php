<?php

namespace mako\image
{
	use \mako\Image;
	use \Exception;
	
	/**
	* Class that manipulates images using ImageMagick.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class ImageMagick
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------

		/**
		* Path to the image.
		*/

		protected $image;

		/**
		* Holds the all the options passed to ImageMagick.
		*/

		protected $cmd = '';

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
				if(function_exists('exec') === false)
				{
					throw new Exception(__CLASS__ . ": The 'exec' function has been disabled.");
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
		* @param   int          Degrees to rotate the image
		* @return  ImageMagick
		*/

		public function rotate($degrees)
		{		
			$this->cmd .= '-rotate '.escapeshellarg((360 - $degrees)).' ';

			return $this;
		}

		/**
		* Resizes the image to the chosen size. 
		*
		* @param   int          Width of the image
		* @param   int          (optional) Height of the image
		* @param   int          (optional) Aspect ratio
		* @return  ImageMagick
		*/

		public function resize($width, $height = null, $aspectRatio = null)
		{
			if($height === null)
			{				
				$this->cmd .= '-resize '.escapeshellarg((int) $width.'%').' ';
			}
			else
			{
				if($aspectRatio === Image::AUTO)
				{
					// Calculate smallest size based on given height and width while maintaining aspect ratio

					$this->cmd .= '-resize '.escapeshellarg((int) $width.'x'.(int) $height).' ';
				}
				else if($aspectRatio === Image::WIDTH)
				{
					// Base new size on given width while maintaining aspect ratio

					$this->cmd .= '-resize '.escapeshellarg((int) $width).' ';
				}
				else if($aspectRatio === Image::HEIGHT)
				{
					// Base new size on given height while maintaining aspect ratio

					$this->cmd .= '-resize '.escapeshellarg('x'.(int) $height).' ';
				}
				else
				{
					// Ignone aspect ratio
					
					$this->cmd .= '-resize '.escapeshellarg((int) $width.'x'.(int) $height.'!').' ';
				}						
			}

			return $this;
		}

		/**
		* Flips the image.
		*
		* @access  public
		* @param   string       (optional) Direction to flip the image
		* @return  ImageMagick
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
		* @param   string  Path to the image file
		* @param   int     (optional) Position of the watermark
		* @param   int     (optional) Opacity of the watermark in percent
		* @return  ImageMagick
		*/
		
		public function watermark($file, $position = null, $opacity = 100)
		{
			// Check if the image exists
			
			if(file_exists($file) === false)
			{
				throw new Exception(__CLASS__ . ": Image file ('{$file}') does not exist.");
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
		* @return  ImageMagick
		*/
		
		public function greyscale()
		{
			$this->cmd .= '-fx \'(r+g+b)/3\' ';
			
			return $this;
		}
		
		/**
		* Adds a border to the image.
		*
		* @access  public
		* @param   string  Hex code for the colour
		* @param   int     Thickness of the frame in pixels
		* @return  ImageMagick
		*/
		
		public function border($colour = '#000', $thickness = 5)
		{
			$this->cmd .= '-shave ' . escapeshellarg($thickness . 'x' . $thickness) . ' -bordercolor ' . escapeshellarg($colour) . ' -border ' . escapeshellarg($thickness) . ' ';
			
			return $this;
		}
		
		/**
		* Saves image to file and in the specified quality.
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

			// Manipulate and save image

			$file      = escapeshellarg($file);
			$quality   = escapeshellarg((int) $quality.'%');
			$this->cmd = trim($this->cmd);

			exec("convert {$this->image} {$this->cmd} -quality {$quality} {$file}", $output, $code);

			if($code !== 0)
			{
				$error = ($code === 127) ? 'ImageMagick could not be found.' : 'An error occured.';

				throw new Exception(__CLASS__ . ": {$error}");
			}
		}
	}
}

/** -------------------- End of file --------------------**/