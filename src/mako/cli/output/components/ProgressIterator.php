<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\components;

use Countable;
use Generator;
use IteratorAggregate;
use mako\cli\output\components\progress\Theme;
use mako\cli\output\components\progress\traits\ProgressTrait;
use mako\cli\output\Output;
use Traversable;

use function count;

/**
 * Progress iterator component.
 */
class ProgressIterator implements IteratorAggregate
{
	use ProgressTrait;

	/**
	 * Item count.
	 */
	protected int $itemCount = 0;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Output $output,
		protected array|(Countable&Traversable) $items,
		protected string $description = '',
		protected int $width = 20,
		protected float $minTimeBetweenRedraw = 0.1,
		protected Theme $theme = new Theme
	) {
		$this->itemCount = count($items);

		if (!empty($description)) {
			$this->description = "{$description} ";
		}
	}

	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		$this->output->restoreCursor();
	}

	/**
	 * Returns the iterator.
	 */
	public function getIterator(): Generator
	{
		$this->output->hideCursor();

		$this->draw();

		foreach ($this->items as $key => $value) {
			yield $key => $value;

			$this->advance();
		}

		$this->output->showCursor();
	}
}
