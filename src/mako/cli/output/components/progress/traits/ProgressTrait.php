<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\components\progress\traits;

use function floor;
use function microtime;
use function min;
use function number_format;
use function str_pad;
use function str_repeat;
use function strlen;

/**
 * Progress trait.
 *
 * @property \mako\cli\output\Output                    $output
 * @property \mako\cli\output\components\progress\Theme $theme
 */
trait ProgressTrait
{
	/**
	 * Time of last redraw.
	 */
	protected ?float $lastRedraw = null;

	/**
	 * Progress status.
	 */
	protected int $progress = 0;

	/**
	 * Builds the progressbar.
	 */
	protected function buildProgressBar(float $percent): string
	{
		$fill = (int) floor($percent * $this->width);

		$progress = number_format($percent * 100, 2, '.');

		return str_pad((string) $this->progress, strlen((string) $this->itemCount), '0', STR_PAD_LEFT)
		. "/{$this->itemCount} "
		. str_repeat($this->theme->getFilled(), $fill)
		. str_repeat($this->theme->getEmpty(), ($this->width - $fill))
		. str_pad(" {$progress}% ", 9, ' ', STR_PAD_LEFT);
	}

	/**
	 * Draws the progressbar.
	 */
	protected function draw(): void
	{
		// Calculate percent

		$percent = (float) ($this->itemCount === 0 ? 1 : min(($this->progress / $this->itemCount), 1));

		// Build progress bar

		$progressBar = $this->buildProgressBar($percent);

		// Draw progressbar

		$this->output->write("\r{$this->description}{$progressBar}");

		// If we're done then we'll add a newline to the output

		if ($this->progress === $this->itemCount) {
			$this->output->write(PHP_EOL);
		}
	}

	/**
	 * Return current unix timestamp with microseconds.
	 */
	protected function getMicrotime(): float
	{
		return microtime(true);
	}

	/**
	 * Should the progress bar be redrawn?
	 */
	protected function shouldRedraw(): bool
	{
		$time = $this->getMicrotime();

		if ($this->lastRedraw === null || $time - $this->lastRedraw >= $this->minTimeBetweenRedraw) {
			$this->lastRedraw = $time;

			return true;
		}

		return false;
	}

	/**
	 * Move progress forward and redraws the progressbar.
	 */
	protected function advance(): void
	{
		$this->progress++;

		if ($this->progress === $this->itemCount || $this->shouldRedraw()) {
			$this->draw();
		}
	}

	/**
	 * Removes the progressbar.
	 */
	public function remove(): void
	{
		$this->output->clearLines($this->progress === $this->itemCount ? 2 : 1);
	}
}
