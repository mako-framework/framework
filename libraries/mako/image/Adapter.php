<?php

namespace mako\image
{
	/**
	* Image adapter.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	abstract class Adapter
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------

		/**
		* Temporary image object.
		*/

		protected $image;

		//---------------------------------------------
		// Class constructor, destructor etc ...
		//---------------------------------------------

		abstract public function __construct($file);

		//---------------------------------------------
		// Class methods
		//---------------------------------------------

		abstract public function rotate($degrees);

		abstract public function resize($width, $height = null, $aspectRatio = null);

		abstract public function crop($width, $height, $x, $y);

		abstract public function flip($direction = null);

		abstract public function watermark($file, $position = null, $opacity = 100);

		abstract public function greyscale();

		abstract public function colorize($color);

		abstract public function border($color = '#000', $thickness = 5);

		abstract public function save($file, $quality = 85);
	}
}

/** -------------------- End of file --------------------**/