<?php

namespace mako\view;

/**
* View blocks.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Block
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	* View blocks.
	*
	* @var array 
	*/

	protected static $blocks = array();

	/**
	* Open view blocks.
	*
	* @var array
	*/

	protected static $openBlocks = array();

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
	* Opens a view block.
	*
	* @access  public
	* @param   string  $name  Block name
	*/

	public static function open($name)
	{
		ob_start() && static::$openBlocks[] = $name;
	}

	/**
	* Closes a view block.
	*
	* @access  public
	*/

	public static function close()
	{
		static::$blocks[array_pop(static::$openBlocks)] = ob_get_clean();
	}

	/**
	* Returns TRUE if the block exists and FALSE if not.
	*
	* @access  public
	* @param   string   $name  Block name
	* @return  boolean
	*/

	public static function exists($name)
	{
		return isset(static::$blocks[$name]);
	}

	/**
	* Returns the contents of a view block.
	*
	* @access  public
	* @param   string  $name  Block name
	* @return  string
	*/

	public static function get($name)
	{
		return isset(static::$blocks[$name]) ? static::$blocks[$name] : '';
	}
}

/** -------------------- End of file --------------------**/