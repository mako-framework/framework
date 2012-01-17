<?php

namespace mako
{
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

		// Nothing here

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
			
			foreach((array) $attributes as $attribute => $value)
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

		public static function tag($name, array $attributes = null, $content = null)
		{
			$close = defined('MAKO_XHTML') ? ' />' : '>';

			return '<' . $name . static::attributes($attributes) . (empty($content) ? $close : '>' . $content . '</' . $name . '>');
		}

		/**
		* Helper method for building media tags.
		*
		* @access  protected
		* @param   string     Tag type
		* @param   mixed      File or array of files
		* @param   array      (optional) Tag attributes
		*/

		protected static function buildMedia($type, $file, $attributes)
		{
			$sources = function($files)
			{
				$sources = '';

				foreach((array) $files as $file)
				{
					$sources .= HTML::tag('source', array('src' => $file));
				}

				return $sources;
			};
			
			return static::tag($type, $attributes, $sources($file));
		}

		/**
		* Creates audio tag with support for multiple sources.
		*
		* @access  public
		* @param   mixed   File or array of files
		* @param   array   (optional) Tag attributes
		*/

		public static function audio($files, array $attributes = null)
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

		public static function video($files, array $attributes = null)
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
					$list .= static::tag('li', null, static::buildList($type, $item, null));
				}
				else
				{
					$list .= static::tag('li', null, $item);
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

		public static function ul(array $items, array $attributes = null)
		{
			return static::buildList('ul', $items, $attributes);
		}

		/**
		* Builds am ordered list.
		*
		* @access  public
		* @param   array   List items
		* @param   array   List attributes
		* @param   string
		*/

		public static function ol(array $items, array $attributes = null)
		{
			return static::buildList('ol', $items, $attributes);
		}
	}
}

/** -------------------- End of file --------------------**/