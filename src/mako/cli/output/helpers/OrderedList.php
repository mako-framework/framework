<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\helpers;

use mako\cli\output\Output;

use function is_array;
use function sprintf;
use function str_repeat;
use function strlen;

/**
 * Ordered list helper.
 */
class OrderedList
{
	/**
	 * Padding.
	 *
	 * @var string
	 */
	protected $padding = '  ';

	/**
	 * Formatter instance.
	 *
	 * @var \mako\cli\output\formatter\FormatterInterface|null
	 */
	protected $formatter;

	/**
	 * Constructor.
	 *
	 * @param \mako\cli\output\Output $output Output instance
	 */
	public function __construct(
		protected Output $output
	)
	{
		$this->formatter = $output->getFormatter();
	}

	/**
	 * Calculates the maximum width of a marker in a list.
	 *
	 * @param  array  $items  Items
	 * @param  string $marker Item marker
	 * @return array
	 */
	protected function calculateWidth(array $items, string $marker): array
	{
		$count = 0;

		foreach($items as $item)
		{
			if(!is_array($item))
			{
				$count++;
			}
		}

		$number = strlen($count);

		$marker = strlen(sprintf($this->formatter === null ? $marker : $this->formatter->stripTags($marker), '')) + $number;

		return ['number' => $number, 'marker' => $marker];
	}

	/**
	 * Builds a list item.
	 *
	 * @param  string $item         Item
	 * @param  string $marker       Item marker
	 * @param  int    $width        Item number width
	 * @param  int    $number       Item number
	 * @param  int    $nestingLevel Nesting level
	 * @param  int    $parentWidth  Parent width
	 * @return string
	 */
	protected function buildListItem(string $item, string $marker, int $width, int $number, int $nestingLevel, int $parentWidth): string
	{
		$marker = str_repeat(' ', $width - strlen($number)) . sprintf($marker, $number);

		return str_repeat($this->padding, $nestingLevel) . str_repeat(' ', $parentWidth) . "{$marker} {$item}" . PHP_EOL;
	}

	/**
	 * Builds an ordered list.
	 *
	 * @param  array  $items        Items
	 * @param  string $marker       Item marker
	 * @param  int    $nestingLevel Nesting level
	 * @param  int    $parentWidth  Parent marker width
	 * @return string
	 */
	protected function buildList(array $items, string $marker, int $nestingLevel = 0, int $parentWidth = 0): string
	{
		$width  = $this->calculateWidth($items, $marker);
		$number = 0;
		$list   = '';

		foreach($items as $item)
		{
			if(is_array($item))
			{
				$list .= $this->buildList($item, $marker, ($nestingLevel + 1), ($width['marker'] - 1 + $parentWidth));
			}
			else
			{
				$list .= $this->buildListItem($item, $marker, $width['number'], ++$number, $nestingLevel, $parentWidth);
			}
		}

		return $list;
	}

	/**
	 * Renders an ordered list.
	 *
	 * @param  array  $items  Items
	 * @param  string $marker Item marker
	 * @return string
	 */
	public function render(array $items, string $marker = '%s.'): string
	{
		return $this->buildList($items, $marker);
	}

	/**
	 * Draws an ordered list.
	 *
	 * @param array  $items  Items
	 * @param string $marker Item marker
	 * @param int    $writer Output writer
	 */
	public function draw(array $items, string $marker = '%s.', int $writer = Output::STANDARD): void
	{
		$this->output->write($this->render($items, $marker), $writer);
	}
}
