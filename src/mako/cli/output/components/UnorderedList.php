<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\components;

use mako\cli\output\Output;

use function is_array;
use function str_repeat;

/**
 * Unordered list component.
 */
class UnorderedList
{
	/**
	 * Padding.
	 */
	protected string $padding = '  ';

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Output $output
	) {
	}

	/**
	 * Builds a list item.
	 */
	protected function buildListItem(string $item, string $marker, int $nestingLevel): string
	{
		return str_repeat($this->padding, $nestingLevel) . "{$marker} {$item}" . PHP_EOL;
	}

	/**
	 * Builds an unordered list.
	 */
	protected function buildList(array $items, string $marker, int $nestingLevel = 0): string
	{
		$list = '';

		foreach ($items as $item) {
			if (is_array($item)) {
				$list .= $this->buildList($item, $marker, $nestingLevel + 1);
			}
			else {
				$list .= $this->buildListItem($item, $marker, $nestingLevel);
			}
		}

		return $list;
	}

	/**
	 * Renders an unordered list.
	 */
	public function render(array $items, string $marker = '*'): string
	{
		return $this->buildList($items, $marker);
	}

	/**
	 * Draws an unordered list.
	 */
	public function draw(array $items, string $marker = '*', int $writer = Output::STANDARD): void
	{
		$this->output->write($this->render($items, $marker), $writer);
	}
}
