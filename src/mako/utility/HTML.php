<?php

namespace mako\utility;

use \Closure;
use \BadMethodCallException;

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
	// Class properties
	//---------------------------------------------

	/**
	 * Custom tags.
	 *
	 * @var array
	 */

	protected static $tags = [];

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
	 * @param   array   $attributes  Array of tags
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
	 * @param   string  $name        Tag name
	 * @param   array   $attributes  (optional) Tag attributes
	 * @param   string  $content     (optional) Tag content
	 * @return  string
	 */

	public static function tag($name, array $attributes = [], $content = null)
	{
		return '<' . $name . static::attributes($attributes) . (($content === null) ? (defined('MAKO_XHTML') ? ' />' : '>') : '>' . $content . '</' . $name . '>');
	}

	/**
	 * Helper method for building media tags.
	 *
	 * @access  protected
	 * @param   string     $type        Tag type
	 * @param   mixed      $files       File or array of files
	 * @param   array      $attributes  (optional) Tag attributes
	 */

	protected static function buildMedia($type, $files, $attributes)
	{
		$sources = '';

		foreach((array) $files as $file)
		{
			$sources .= HTML::tag('source', ['src' => $file]);
		}
		
		return static::tag($type, $attributes, $sources);
	}

	/**
	 * Creates audio tag with support for multiple sources.
	 *
	 * @access  public
	 * @param   mixed   $files       File or array of files
	 * @param   array   $attributes  (optional) Tag attributes
	 */

	public static function audio($files, array $attributes = [])
	{
		return static::buildMedia('audio', $files, $attributes);
	}

	/**
	 * Creates video tag with support for multiple sources.
	 *
	 * @access  public
	 * @param   mixed   $files       File or array of files
	 * @param   array   $attributes  (optional) Tag attributes
	 */

	public static function video($files, array $attributes = [])
	{
		return static::buildMedia('video', $files, $attributes);
	}

	/**
	 * Helper method for building list tags.
	 *
	 * @access  protected
	 * @param   string     $type        Tag type
	 * @param   mixed      $items       File or array of files
	 * @param   array      $attributes  (optional) Tag attributes
	 */

	protected static function buildList($type, $items, $attributes)
	{
		$list = '';

		foreach($items as $item)
		{
			if(is_array($item))
			{
				$list .= static::tag('li', [], static::buildList($type, $item, []));
			}
			else
			{
				$list .= static::tag('li', [], $item);
			}
		}

		return static::tag($type, $attributes, $list);
	}

	/**
	 * Builds an un-ordered list.
	 *
	 * @access  public
	 * @param   array   $items       List items
	 * @param   array   $attributes  List attributes
	 * @return  string
	 */

	public static function ul(array $items, array $attributes = [])
	{
		return static::buildList('ul', $items, $attributes);
	}

	/**
	 * Builds am ordered list.
	 *
	 * @access  public
	 * @param   array   $items       List items
	 * @param   array   $attributes  List attributes
	 * @return  string
	 */

	public static function ol(array $items, array $attributes = [])
	{
		return static::buildList('ol', $items, $attributes);
	}

	/**
	 * Registers a new HTML tag.
	 *
	 * @access  public
	 * @param   string   $name     Tag name
	 * @param   \Closure  $closure  Tag closure
	 */

	public static function registerTag($name, Closure $tag)
	{
		static::$tags[$name] = $tag;
	}

	/**
	 * Magic shortcut to the custom HTML macros.
	 *
	 * @access  public
	 * @param   string  $name       Method name
	 * @param   array   $arguments  Method arguments
	 * @return  mixed
	 */

	public static function __callStatic($name, $arguments)
	{
		if(!isset(static::$tags[$name]))
		{
			throw new BadMethodCallException(vsprintf("Call to undefined method %s::%s().", [__CLASS__, $name]));
		}

		return call_user_func_array(static::$tags[$name], $arguments);
	}
}

/** -------------------- End of file -------------------- **/