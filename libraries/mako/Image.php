<?php

namespace mako
{
	use \RuntimeException;
	
	/**
	* Image factory class.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2012 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class Image
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------
		
		/**
		* Resizing contraint.
		*/
		
		const IGNORE = 10;
		
		/**
		* Resizing contraint.
		*/
		
		const AUTO = 11;
		
		/**
		* Resizing contraint.
		*/
		
		const WIDTH = 12;
		
		/**
		* Resizing contraint.
		*/
		
		const HEIGHT = 13;
		
		/**
		* Watermark position.
		*/

		const TOP_LEFT = 20;
		
		/**
		* Watermark position.
		*/
		
		const TOP_RIGHT = 21;
		
		/**
		* Watermark position.
		*/
		
		const BOTTOM_LEFT = 22;
		
		/**
		* Watermark position.
		*/
		
		const BOTTOM_RIGHT = 23;
		
		/**
		* Watermark position.
		*/
		
		const CENTER = 24;
		
		/**
		* Flip direction.
		*/
		
		const VERTICAL = 30;
		
		/**
		* Flip direction.
		*/
		
		const HORIZONTAL = 31;

		//---------------------------------------------
		// Class constructor, destructor etc ...
		//---------------------------------------------

		/**
		* Protected constructor since this is a static class.
		*
		* @access  protected
		*/

		protected function __construct()
		{
			// Nothing here
		}

		//---------------------------------------------
		// Class methods
		//---------------------------------------------

		/**
		* Factory method that returns a image object.
		*
		* @param   string  Path to the image file
		* @param   string  (optional) Image library
		* @return  object
		*/

		public static function factory($file, $library = 'GD')
		{
			// Check if the image exists

			if(file_exists($file) === false)
			{
				throw new RuntimeException(__CLASS__ . ": Image file ('{$file}') does not exist.");
			}

			// Create and return image object

			$class = '\mako\image\\' . $library;

			return new $class($file);
		}
	}
}

/** -------------------- End of file --------------------**/