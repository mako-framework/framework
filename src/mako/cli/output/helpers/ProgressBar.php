<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\helpers;

use mako\cli\exceptions\CliException;
use mako\cli\output\Output;

use function floor;
use function max;
use function microtime;
use function min;
use function str_pad;
use function str_repeat;
use function strlen;

/**
 * Progress bar helper.
 */
class ProgressBar
{
	/**
	 * Progressbar width.
	 */
	protected int $width = 20;

	/**
	 * String that represents the empty part of the progess bar.
	 */
	protected string $emptyTemplate = '-';

	/**
	 * String that represents the filled part of the progess bar.
	 */
	protected string $filledTemplate = '=';

	/**
	 * Minimum time between redraw in seconds.
	 */
	protected float $minTimeBetweenRedraw;

	/**
	 * Time of last redraw.
	 */
	protected float|null $lastRedraw = null;

	/**
	 * Progress status.
	 */
	protected int $progress = 0;

	/**
	 * Progress bar prefix.
	 */
	protected string $prefix = '';

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Output $output,
		protected int $items,
		float $minTimeBetweenRedraw = 0.1
	)
	{
		$this->minTimeBetweenRedraw = max(min($minTimeBetweenRedraw, 1), 0.1);
	}

	/**
	 * Sets the progress bar width.
	 */
	public function setWidth(int $width): void
	{
		$this->width = $width;
	}

	/**
	 * Sets the string that represents the empty part of the progess bar.
	 */
	public function setEmptyTemplate(string $template): void
	{
		$this->emptyTemplate = $template;
	}

	/**
	 * Sets the string that represents the filled part of the progess bar.
	 */
	public function setFilledTemplate(string $template): void
	{
		$this->filledTemplate = $template;
	}

	/**
	 * Sets the progress bar prefix.
	 */
	public function setPrefix(string $prefix): void
	{
		$this->prefix = "{$prefix} ";
	}

	/**
	 * Builds the progressbar.
	 */
	protected function buildProgressBar(float $percent): string
	{
		$fill = (int) floor($percent * $this->width);

		return str_pad($this->progress, strlen($this->items), '0', STR_PAD_LEFT)
		. "/{$this->items} "
		. str_repeat($this->filledTemplate, $fill)
		. str_repeat($this->emptyTemplate, ($this->width - $fill))
		. str_pad(' ' . ((int) ($percent * 100)) . '% ', 6, ' ', STR_PAD_LEFT);
	}

	/**
	 * Draws the progressbar.
	 */
	public function draw(): void
	{
		// Don't draw progess bar if there are 0 items

		if($this->items === 0)
		{
			return;
		}

		// Calculate percent

		$percent = (float) min(($this->progress / $this->items), 1);

		// Build progress bar

		$progressBar = $this->buildProgressBar($percent);

		// Draw progressbar

		$this->output->write("\r{$this->prefix}{$progressBar}");

		// If we're done then we'll add a newline to the output

		if($this->progress === $this->items)
		{
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

		if($this->lastRedraw === null || $time - $this->lastRedraw >= $this->minTimeBetweenRedraw)
		{
			$this->lastRedraw = $time;

			return true;
		}

		return false;
	}

	/**
	 * Move progress forward and redraws the progressbar.
	 */
	public function advance(): void
	{
		$this->progress++;

		if($this->progress > $this->items)
		{
			throw new CliException('You cannot advance past 100%.');
		}

		if($this->progress === $this->items || $this->shouldRedraw())
		{
			$this->draw();
		}
	}

	/**
	 * Removes the progressbar.
	 */
	public function remove(): void
	{
		$this->output->clearLines($this->progress === $this->items ? 2 : 1);
	}
}
