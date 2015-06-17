<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cli\output\helpers;

use mako\cli\output\Output;

/**
 * Progress bar helper.
 *
 * @author  Frederic G. Østby
 */

class ProgressBar
{
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
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\cli\output\Output  $output      Output instance
	 * @param   int                      $items       Total number of items
	 * @param   int                      $redrawRate  Redraw rate
	 */

	public function __construct(Output $output, $items, $redrawRate = null)
	{
		$this->output = $output;

		$this->items = $items;

		$this->redrawRate = max($redrawRate ?: ceil(0.01 * $items), 1);
	}

	/**
	 * Sets the string that represents the empty part of the progess bar.
	 *
	 * @access  public
	 * @param   string  $template  Template
	 */

	public function setEmptyTemplate($template)
	{
		$this->emptyTemplate = $template;
	}

	/**
	 * Sets the string that represents the filled part of the progess bar.
	 *
	 * @access  public
	 * @param   string  $template  Template
	 */

	public function setFilledTemplate($template)
	{
		$this->filledTemplate = $template;
	}

	/**
	 * Builds the progressbar.
	 *
	 * @access  protected
	 * @param   int        $percent  Percent to fill
	 * @return  string
	 */

	protected function buildProgressBar($percent)
	{
		$fill = (int) floor($percent / 5);

		$progressBar  = str_pad($this->progress, strlen($this->items), '0', STR_PAD_LEFT) . '/' . $this->items . ' ';

		$progressBar .= str_repeat($this->filledTemplate, $fill);

		$progressBar .= str_repeat($this->emptyTemplate, (20 - $fill));

		$progressBar .= str_pad(' '. $percent . '% ', 6, ' ', STR_PAD_LEFT);

		return $progressBar;
	}

	/**
	 * Draws the progressbar.
	 *
	 * @access  public
	 */

	public function draw()
	{
		// Don't draw progess bar if there are 0 items

		if($this->items === 0)
		{
			return;
		}

		// Calculate percent

		$percent = (int) ceil(min(($this->progress / $this->items) * 100, 100));

		// Build progress bar

		$progressBar = $this->buildProgressBar($percent);

		// Draw progressbar

		$this->output->write("\r" . $progressBar);

		// If we're done then we'll add a newline to the output

		if($this->progress === $this->items)
		{
			$this->output->write(PHP_EOL);
		}
	}

	/**
	 * Move progress forward and redraws the progessbar.
	 *
	 * @access  public
	 */

	public function advance()
	{
		$this->progress++;

		if($this->progress === $this->items || ($this->progress % $this->redrawRate) === 0)
		{
			$this->draw();
		}
	}
}