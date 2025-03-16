<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\components;

use mako\cli\exceptions\CliException;
use mako\cli\output\components\progress\Theme;
use mako\cli\output\components\progress\traits\ProgressTrait;
use mako\cli\output\Output;

/**
 * Progress component.
 */
class Progress
{
	use ProgressTrait {
		draw as baseDraw;
		advance as baseAdvance;
	}

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Output $output,
		protected int $itemCount,
		protected string $description = '',
		protected int $width = 20,
		protected float $minTimeBetweenRedraw = 0.1,
		protected Theme $theme = new Theme
	) {
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
	 * Draws the progressbar.
	 */
	public function draw(): void
	{
		if ($this->itemCount === 0) {
			return;
		}

		if ($this->progress === 0) {
			$this->output->hideCursor();
		}

		$this->baseDraw();
	}

	/**
	 * Move progress forward and redraws the progressbar.
	 */
	public function advance(): void
	{
		if ($this->progress + 1 > $this->itemCount) {
			throw new CliException('You cannot advance past 100%.');
		}

		$this->baseAdvance();

		if ($this->progress === $this->itemCount) {
			$this->output->showCursor();
		}
	}
}
