<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\utility;

use Closure;
use BadMethodCallException;

/**
 * HTML helper.
 *
 * @author  Frederic G. Østby
 */

class HTML
{
	/**
	 * Should we return XHTML?
	 *
	 * @var boolean
	 */

	protected $xhtml;

	/**
	 * Custom tags.
	 *
	 * @var array
	 */

	protected static $tags = [];

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   boolean  $xhtml  Should we return HXML?
	 */

	public function __construct($xhtml = false)
	{
		$this->xhtml = $xhtml;
	}

	/**
	 * Registers a new HTML tag.
	 *
	 * @access  public
	 * @param   string    $name  Tag name
	 * @param   \Closure  $tag   Tag closure
	 */

	public static function registerTag($name, Closure $tag)
	{
		static::$tags[$name] = $tag;
	}

	/**
	 * Takes an array of attributes and turns it into a string.
	 *
	 * @access  public
	 * @param   array   $attributes  Array of tags
	 * @return  string
	 */

	protected function attributes($attributes)
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
	 * @param   array   $attributes  Tag attributes
	 * @param   string  $content     Tag content
	 * @return  string
	 */

	public function tag($name, array $attributes = [], $content = null)
	{
		return '<' . $name . $this->attributes($attributes) . (($content === null) ? ($this->xhtml ? ' />' : '>') : '>' . $content . '</' . $name . '>');
	}

	/**
	 * Helper method for building media tags.
	 *
	 * @access  protected
	 * @param   string     $type        Tag type
	 * @param   mixed      $files       File or array of files
	 * @param   array      $attributes  Tag attributes
	 */

	protected function buildMedia($type, $files, $attributes)
	{
		$sources = '';

		foreach((array) $files as $file)
		{
			$sources .= $this->tag('source', ['src' => $file]);
		}

		return $this->tag($type, $attributes, $sources);
	}

	/**
	 * Creates audio tag with support for multiple sources.
	 *
	 * @access  public
	 * @param   mixed   $files       File or array of files
	 * @param   array   $attributes  Tag attributes
	 */

	public function audio($files, array $attributes = [])
	{
		return $this->buildMedia('audio', $files, $attributes);
	}

	/**
	 * Creates video tag with support for multiple sources.
	 *
	 * @access  public
	 * @param   mixed   $files       File or array of files
	 * @param   array   $attributes  Tag attributes
	 */

	public function video($files, array $attributes = [])
	{
		return $this->buildMedia('video', $files, $attributes);
	}

	/**
	 * Helper method for building list tags.
	 *
	 * @access  protected
	 * @param   string     $type        Tag type
	 * @param   mixed      $items       File or array of files
	 * @param   array      $attributes  Tag attributes
	 */

	protected function buildList($type, $items, $attributes)
	{
		$list = '';

		foreach($items as $item)
		{
			if(is_array($item))
			{
				$list .= $this->tag('li', [], $this->buildList($type, $item, []));
			}
			else
			{
				$list .= $this->tag('li', [], $item);
			}
		}

		return $this->tag($type, $attributes, $list);
	}

	/**
	 * Builds an un-ordered list.
	 *
	 * @access  public
	 * @param   array   $items       List items
	 * @param   array   $attributes  List attributes
	 * @return  string
	 */

	public function ul(array $items, array $attributes = [])
	{
		return $this->buildList('ul', $items, $attributes);
	}

	/**
	 * Builds am ordered list.
	 *
	 * @access  public
	 * @param   array   $items       List items
	 * @param   array   $attributes  List attributes
	 * @return  string
	 */

	public function ol(array $items, array $attributes = [])
	{
		return $this->buildList('ol', $items, $attributes);
	}

	/**
	 * Magic shortcut to the custom HTML macros.
	 *
	 * @access  public
	 * @param   string  $name       Method name
	 * @param   array   $arguments  Method arguments
	 * @return  string
	 */

	public function __call($name, $arguments)
	{
		if(!isset(static::$tags[$name]))
		{
			throw new BadMethodCallException(vsprintf("Call to undefined method %s::%s().", [__CLASS__, $name]));
		}

		array_unshift($arguments, $this);

		return call_user_func_array(static::$tags[$name], $arguments);
	}
}