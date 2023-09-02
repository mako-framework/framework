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
 */
class HTML
{
	use ExtendableTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected bool $xhtml = false
	)
	{}

	/**
	 * Takes an array of attributes and turns it into a string.
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

			$attr .= " {$attribute}=\"{$value}\"";
		}

		return $attr;
	}

	/**
	 * Creates a HTML5 tag.
	 */
	public function tag(string $name, array $attributes = [], ?string $content = null): string
	{
		return "<{$name}{$this->attributes($attributes)}" . (($content === null) ? ($this->xhtml ? ' />' : '>') : ">{$content}</{$name}>");
	}

	/**
	 * Helper method for building media tags.
	 */
	protected function buildMedia(string $type, array|string $files, array $attributes): string
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
	 */
	public function audio(array|string $files, array $attributes = []): string
	{
		return $this->buildMedia('audio', $files, $attributes);
	}

	/**
	 * Creates video tag with support for multiple sources.
	 */
	public function video(array|string $files, array $attributes = []): string
	{
		return $this->buildMedia('video', $files, $attributes);
	}

	/**
	 * Helper method for building list tags.
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
	 */
	public function ul(array $items, array $attributes = []): string
	{
		return $this->buildList('ul', $items, $attributes);
	}

	/**
	 * Builds am ordered list.
	 */
	public function ol(array $items, array $attributes = []): string
	{
		return $this->buildList('ol', $items, $attributes);
	}
}
