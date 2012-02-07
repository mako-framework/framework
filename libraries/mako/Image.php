<?php

namespace mako;

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
	*
	* @var int
	*/
	
	const IGNORE = 10;
	
	/**
	* Resizing contraint.
	*
	* @var int
	*/
	
	const AUTO = 11;
	
	/**
	* Resizing contraint.
	*
	* @var int
	*/
	
	const WIDTH = 12;
	
	/**
	* Resizing contraint.
	*
	* @var int
	*/
	
	const HEIGHT = 13;
	
	/**
	* Watermark position.
	*
	* @var int
	*/

	const TOP_LEFT = 20;
	
	/**
	* Watermark position.
	*
	* @var int
	*/
	
	const TOP_RIGHT = 21;
	
	/**
	* Watermark position.
	*
	* @var int
	*/
	
	const BOTTOM_LEFT = 22;
	
	/**
	* Watermark position.
	*
	* @var int
	*/
	
	const BOTTOM_RIGHT = 23;
	
	/**
	* Watermark position.
	*
	* @var int
	*/
	
	const CENTER = 24;
	
	/**
	* Flip direction.
	*
	* @var int
	*/
	
	const VERTICAL = 30;
	
	/**
	* Flip direction.
	*
	* @var int
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
	* @param   string              Path to the image file
	* @param   string              (optional) Image library
	* @return  mako\image\Adapter
	*/

	public static function factory($file, $library = 'GD')
	{
		// Check if the image exists

		if(file_exists($file) === false)
		{
			throw new RuntimeException(vsprintf("%s(): The image file ('%s') does not exist.", array(__METHOD__, $file)));
		}

		// Create and return image object

		$class = '\mako\image\\' . $library;

		return new $class($file);
	}
}

/** -------------------- End of file --------------------**/