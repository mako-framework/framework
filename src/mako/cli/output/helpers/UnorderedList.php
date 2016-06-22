<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cli\output\helpers;

use mako\cli\output\Output;

/**
 * Unordered list helper.
 *
 * @author  Frederic G. Østby
 */
class UnorderedList
{
	/**
	 * Padding.
	 *
	 * @var string
	 */
	protected $padding = '  ';

	/**
	 * Output instance.
	 *
	 * @var \mako\cli\output\Output
	 */
	protected $output;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\cli\output\Output  $output  Output instance
	 */
	public function __construct(Output $output)
	{
		$this->output = $output;
	}

	/**
	 * Builds a list item.
	 *
	 * @access  protected
	 * @param   string     $item          Item
	 * @param   string     $marker        Item marker
	 * @param   int        $nestingLevel  Nesting level
	 * @return  string
	 */
	protected function buildListItem(string $item, string $marker, int $nestingLevel): string
	{
		return str_repeat($this->padding, $nestingLevel) . $marker . ' ' . $item . PHP_EOL;
	}

	/**
	 * Builds an unordered list.
	 *
	 * @access  protected
	 * @param   array      $items         Items
	 * @param   string     $marker        Item marker
	 * @param   int        $nestingLevel  Nesting level
	 * @return  string
	 */
	protected function buildList(array $items, string $marker, int $nestingLevel = 0): string
	{
		$list = '';

		foreach($items as $item)
		{
			if(is_array($item))
			{
				$list .= $this->buildList($item, $marker, $nestingLevel + 1);
			}
			else
			{
				$list .= $this->buildListItem($item, $marker, $nestingLevel);
			}
		}

		return $list;
	}

	/**
	 * Renders an unordered list.
	 *
	 * @access  public
	 * @param   array   $items   Items
	 * @param   string  $marker  Item marker
	 * @return  string
	 */
	public function render(array $items, string $marker = '*'): string
	{
		return $this->buildList($items, $marker);
	}

	/**
	 * Draws an unordered list.
	 *
	 * @access  public
	 * @param   array   $items   Items
	 * @param   string  $marker  Item marker
	 * @param   int     $writer  Output writer
	 */
	public function draw(array $items, string $marker = '*', int $writer = Output::STANDARD)
	{
		$this->output->write($this->render($items, $marker), $writer);
	}
}