<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\helpers;

use mako\cli\output\Output;

use function ceil;
use function floor;
use function max;
use function min;
use function str_pad;
use function str_repeat;
use function strlen;

/**
 * Progress bar helper.
 *
 * @author Frederic G. Østby
 */
class ProgressBar
{
	/**
	 * Progressbar width.
	 *
	 * @var int
	 */
	protected $width = 20;

	/**
	 * String that represents the empty part of the progess bar.
	 *
	 * @var string
	 */
	protected $emptyTemplate = '-';

	/**
	 * String that represents the filled part of the progess bar.
	 *
	 * @var string
	 */
	protected $filledTemplate = '=';

	/**
	 * Number of items.
	 *
	 * @var int
	 */
	protected $items;

	/**
	 * Redraw rate.
	 *
	 * @var int
	 */
	protected $redrawRate;

	/**
	 * Progress status.
	 *
	 * @var int
	 */
	protected $progress = 0;

	/**
	 * Output instance.
	 *
	 * @var \mako\cli\output\Output
	 */
	protected $output;

	/**
	 * Progress bar prefix.
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * Constructor.
	 *
	 * @param \mako\cli\output\Output $output     Output instance
	 * @param int                     $items      Total number of items
	 * @param int|null                $redrawRate Redraw rate
	 */
	public function __construct(Output $output, int $items, ?int $redrawRate = null)
	{
		$this->output = $output;

		$this->items = $items;

		$this->redrawRate = max($redrawRate ?? ceil(0.01 * $items), 1);
	}

	/**
	 * Sets the progress bar width.
	 *
	 * @param int $width Progress bar width
	 */
	public function setWidth(int $width): void
	{
		$this->width = $width;
	}

	/**
	 * Sets the string that represents the empty part of the progess bar.
	 *
	 * @param string $template Template
	 */
	public function setEmptyTemplate(string $template): void
	{
		$this->emptyTemplate = $template;
	}

	/**
	 * Sets the string that represents the filled part of the progess bar.
	 *
	 * @param string $template Template
	 */
	public function setFilledTemplate(string $template): void
	{
		$this->filledTemplate = $template;
	}

	/**
	 * Sets the progress bar prefix.
	 *
	 * @param string $prefix Progress bar prefix
	 */
	public function setPrefix(string $prefix): void
	{
		$this->prefix = "{$prefix} ";
	}

	/**
	 * Builds the progressbar.
	 *
	 * @param  float  $percent Percent to fill
	 * @return string
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
	 * Move progress forward and redraws the progressbar.
	 */
	public function advance(): void
	{
		$this->progress++;

		if($this->progress === $this->items || ($this->progress % $this->redrawRate) === 0)
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
