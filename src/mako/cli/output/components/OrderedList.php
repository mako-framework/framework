<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\components;

use mako\cli\output\Output;

use function is_array;
use function sprintf;
use function str_repeat;
use function strlen;

/**
 * Ordered list component.
 */
class OrderedList
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
	 * Calculates the maximum width of a marker in a list.
	 *
	 * @return array{number: int, marker: int}
	 */
	protected function calculateWidth(array $items, string $marker): array
	{
		$count = 0;

		foreach ($items as $item) {
			if (!is_array($item)) {
				$count++;
			}
		}

		$number = strlen((string) $count);

		$marker = strlen(sprintf($this->output->formatter === null ? $marker : $this->output->formatter->stripTags($marker), '')) + $number;

		return ['number' => $number, 'marker' => $marker];
	}

	/**
	 * Builds a list item.
	 */
	protected function buildListItem(string $item, string $marker, int $width, int $number, int $nestingLevel, int $parentWidth): string
	{
		$marker = str_repeat(' ', $width - strlen((string) $number)) . sprintf($marker, $number);

		return str_repeat($this->padding, $nestingLevel) . str_repeat(' ', $parentWidth) . "{$marker} {$item}" . PHP_EOL;
	}

	/**
	 * Builds an ordered list.
	 */
	protected function buildList(array $items, string $marker, int $nestingLevel = 0, int $parentWidth = 0): string
	{
		$width  = $this->calculateWidth($items, $marker);
		$number = 0;
		$list   = '';

		foreach ($items as $item) {
			if (is_array($item)) {
				$list .= $this->buildList($item, $marker, ($nestingLevel + 1), ($width['marker'] - 1 + $parentWidth));
			}
			else {
				$list .= $this->buildListItem($item, $marker, $width['number'], ++$number, $nestingLevel, $parentWidth);
			}
		}

		return $list;
	}

	/**
	 * Renders an ordered list.
	 */
	public function render(array $items, string $marker = '%s.'): string
	{
		return $this->buildList($items, $marker);
	}

	/**
	 * Draws an ordered list.
	 */
	public function draw(array $items, string $marker = '%s.', int $writer = Output::STANDARD): void
	{
		$this->output->write($this->render($items, $marker), $writer);
	}
}
