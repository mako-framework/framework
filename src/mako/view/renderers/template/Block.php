<?php

namespace mako\view\renderers\template;

/**
 * Template blocks.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Block
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Template blocks.
	 *
	 * @var array 
	 */

	protected static $blocks = array();

	/**
	 * Open template blocks.
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
	 * Opens a template block.
	 *
	 * @access  public
	 * @param   string  $name  Block name
	 */

	public static function open($name)
	{
		ob_start() && static::$openBlocks[] = $name;
	}

	/**
	 * Closes a template block.
	 *
	 * @access  public
	 */

	public static function close()
	{
		static::$blocks[array_pop(static::$openBlocks)] = ob_get_clean();
	}

	/**
	 * Returns the contents of a template block.
	 *
	 * @access  public
	 * @param   string  $name    Block name
	 * @param   string  $parent  Parent content
	 * @return  string
	 */

	public static function get($name, $parent)
	{
		return isset(static::$blocks[$name]) ? str_replace('__PARENT__', stripslashes($parent), static::$blocks[$name]) : stripslashes($parent);
	}
}

/** -------------------- End of file -------------------- **/