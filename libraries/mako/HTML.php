<?php

namespace mako;

use \Closure;
use \RuntimeException;

/**
* HTML helper.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2011 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class HTML
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	* Custom macros.
	*
	* @var array
	*/

	protected static $macros = array();

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
	* Takes an array of attributes and turns it into a string.
	*
	* @access  public
	* @param   array   Array of tags
	* @return  string
	*/

	protected static function attributes($attributes)
	{
		$attr = '';
		
		foreach($attributes as $attribute => $value)
		{
			if(is_int($attribute))
			{
				$attribute = $value;
			}
			
			$attr .= ' ' . $attribute . '="' . $value . '"';
		}

		return $attr;
	}

	/**
	* Creates a HTML5 tag.
	*
	* @access  public
	* @param   string  Tag name
	* @param   array   (optional) Tag attributes
	* @param   string  (optional) Tag content
	* @return  string
	*/

	public static function tag($name, array $attributes = array(), $content = null)
	{
		return '<' . $name . static::attributes($attributes) . (($content === null) ? (defined('MAKO_XHTML') ? '/>' : '>') : '>' . $content . '</' . $name . '>');
	}

	/**
	* Helper method for building media tags.
	*
	* @access  protected
	* @param   string     Tag type
	* @param   mixed      File or array of files
	* @param   array      (optional) Tag attributes
	*/

	protected static function buildMedia($type, $files, $attributes)
	{
		$sources = '';

		foreach((array) $files as $file)
		{
			$sources .= HTML::tag('source', array('src' => $file));
		}
		
		return static::tag($type, $attributes, $sources);
	}

	/**
	* Creates audio tag with support for multiple sources.
	*
	* @access  public
	* @param   mixed   File or array of files
	* @param   array   (optional) Tag attributes
	*/

	public static function audio($files, array $attributes = array())
	{
		return static::buildMedia('audio', $files, $attributes);
	}

	/**
	* Creates video tag with support for multiple sources.
	*
	* @access  public
	* @param   mixed   File or array of files
	* @param   array   (optional) Tag attributes
	*/

	public static function video($files, array $attributes = array())
	{
		return static::buildMedia('video', $files, $attributes);
	}

	/**
	* Helper method for building list tags.
	*
	* @access  protected
	* @param   string     Tag type
	* @param   mixed      File or array of files
	* @param   array      (optional) Tag attributes
	*/

	protected static function buildList($type, $items, $attributes)
	{
		$list = '';

		foreach($items as $item)
		{
			if(is_array($item))
			{
				$list .= static::tag('li', array(), static::buildList($type, $item, array()));
			}
			else
			{
				$list .= static::tag('li', array(), $item);
			}
		}

		return static::tag($type, $attributes, $list);
	}

	/**
	* Builds an un-ordered list.
	*
	* @access  public
	* @param   array   List items
	* @param   array   List attributes
	* @return  string
	*/

	public static function ul(array $items, array $attributes = array())
	{
		return static::buildList('ul', $items, $attributes);
	}

	/**
	* Builds am ordered list.
	*
	* @access  public
	* @param   array   List items
	* @param   array   List attributes
	* @return  string
	*/

	public static function ol(array $items, array $attributes = array())
	{
		return static::buildList('ol', $items, $attributes);
	}

	/**
	* Registers a new HTML macro.
	*
	* @access  public
	* @param   string   Macro name
	* @param   Closure  Macro closure
	*/

	public static function macro($name, Closure $macro)
	{
		static::$macros[$name] = $macro;
	}

	/**
	* Magic shortcut to the custom HTML macros.
	*
	* @access  public
	* @param   string  Method name
	* @param   array   Method arguments
	* @return  mixed
	*/

	public static function __callStatic($name, $arguments)
	{
		if(!isset(static::$macros[$name]))
		{
			throw new RuntimeException(vsprintf("Call to undefined method %s::%s().", array(__CLASS__, $name)));
		}

		return call_user_func_array(static::$macros[$name], $arguments);
	}
}

/** -------------------- End of file --------------------**/