<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\utility;

use mako\common\traits\ExtendableTrait;

use function is_array;
use function is_int;

/**
 * HTML helper.
 *
 * @author Frederic G. Østby
 */
class HTML
{
	use ExtendableTrait;

	/**
	 * Should we return XHTML?
	 *
	 * @var bool
	 */
	protected $xhtml;

	/**
	 * Constructor.
	 *
	 * @param bool $xhtml Should we return HXML?
	 */
	public function __construct(bool $xhtml = false)
	{
		$this->xhtml = $xhtml;
	}

	/**
	 * Takes an array of attributes and turns it into a string.
	 *
	 * @param  array  $attributes Array of tags
	 * @return string
	 */
	protected function attributes(array $attributes): string
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
	 * @param  string      $name       Tag name
	 * @param  array       $attributes Tag attributes
	 * @param  string|null $content    Tag content
	 * @return string
	 */
	public function tag(string $name, array $attributes = [], ?string $content = null): string
	{
		return '<' . $name . $this->attributes($attributes) . (($content === null) ? ($this->xhtml ? ' />' : '>') : '>' . $content . '</' . $name . '>');
	}

	/**
	 * Helper method for building media tags.
	 *
	 * @param  string       $type       Tag type
	 * @param  string|array $files      File or array of files
	 * @param  array        $attributes Tag attributes
	 * @return string
	 */
	protected function buildMedia(string $type, $files, array $attributes): string
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
	 * @param  string|array $files      File or array of files
	 * @param  array        $attributes Tag attributes
	 * @return string
	 */
	public function audio($files, array $attributes = []): string
	{
		return $this->buildMedia('audio', $files, $attributes);
	}

	/**
	 * Creates video tag with support for multiple sources.
	 *
	 * @param  string|array $files      File or array of files
	 * @param  array        $attributes Tag attributes
	 * @return string
	 */
	public function video($files, array $attributes = []): string
	{
		return $this->buildMedia('video', $files, $attributes);
	}

	/**
	 * Helper method for building list tags.
	 *
	 * @param  string $type       Tag type
	 * @param  array  $items      List items
	 * @param  array  $attributes Tag attributes
	 * @return string
	 */
	protected function buildList(string $type, array $items, array $attributes): string
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
	 * @param  array  $items      List items
	 * @param  array  $attributes List attributes
	 * @return string
	 */
	public function ul(array $items, array $attributes = []): string
	{
		return $this->buildList('ul', $items, $attributes);
	}

	/**
	 * Builds am ordered list.
	 *
	 * @param  array  $items      List items
	 * @param  array  $attributes List attributes
	 * @return string
	 */
	public function ol(array $items, array $attributes = []): string
	{
		return $this->buildList('ol', $items, $attributes);
	}
}
