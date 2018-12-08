<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\reactor\traits;

use mako\cli\input\helpers\Confirmation;
use mako\cli\input\helpers\Question;
use mako\cli\input\helpers\Secret;
use mako\cli\input\helpers\Select;
use mako\cli\output\helpers\Bell;
use mako\cli\output\helpers\Countdown;
use mako\cli\output\helpers\OrderedList;
use mako\cli\output\helpers\ProgressBar;
use mako\cli\output\helpers\Table;
use mako\cli\output\helpers\UnorderedList;
use mako\cli\output\Output;
use mako\syringe\traits\ContainerAwareTrait;

use function str_repeat;

/**
 * Controller helper trait.
 *
 * @author Frederic G. Østby
 */
trait CommandHelperTrait
{
	use ContainerAwareTrait;

	/**
	 * Writes n newlines to output.
	 *
	 * @param int $lines  Number of newlines to write
	 * @param int $writer Output writer
	 */
	protected function nl(int $lines = 1, int $writer = Output::STANDARD): void
	{
		$this->output->write(str_repeat(PHP_EOL, $lines), $writer);
	}

	/**
	 * Writes string to output.
	 *
	 * @param string $string String to write
	 * @param int    $writer Output writer
	 */
	protected function write(string $string, int $writer = Output::STANDARD): void
	{
		$this->output->writeLn($string, $writer);
	}

	/**
	 * Writes string to output using the error writer.
	 *
	 * @param string $string String to write
	 */
	protected function error(string $string): void
	{
		$this->output->errorLn('<red>' . $string . '</red>');
	}

	/**
	 * Clears the screen.
	 */
	protected function clear(): void
	{
		$this->output->clear();
	}

	/**
	 * Rings the terminal bell n times.
	 *
	 * @param int $times Number of times to ring the bell
	 */
	protected function bell(int $times = 1): void
	{
		(new Bell($this->output))->ring($times);
	}

	/**
	 * Counts down from n.
	 *
	 * @param int $from Number of seconds to count down
	 */
	protected function countdown(int $from = 5): void
	{
		(new Countdown($this->output))->draw($from);
	}

	/**
	 * Draws a progress bar and returns a progress bar instance.
	 *
	 * @param  int                                  $items      Total number of items
	 * @param  int|null                             $redrawRate Redraw rate
	 * @param  string|null                          $prefix     Progress bar prefix
	 * @return \mako\cli\output\helpers\ProgressBar
	 */
	protected function progressBar(int $items, ?int $redrawRate = null, ?string $prefix = null): ProgressBar
	{
		$progressBar = new ProgressBar($this->output, $items, $redrawRate);

		$progressBar->setWidth(50);

		$progressBar->setEmptyTemplate('<red>░</red>');

		$progressBar->setFilledTemplate('<green>▓</green>');

		if($prefix !== null)
		{
			$progressBar->setPrefix($prefix);
		}

		$progressBar->draw();

		return $progressBar;
	}

	/**
	 * Draws a table.
	 *
	 * @param array $columnNames Array of column names
	 * @param array $rows        Array of rows
	 * @param int   $writer      Output writer
	 */
	protected function table(array $columnNames, array $rows, int $writer = Output::STANDARD): void
	{
		(new Table($this->output))->draw($columnNames, $rows, $writer);
	}

	/**
	 * Draws an ordered list.
	 *
	 * @param array  $items  Items
	 * @param string $marker Item marker
	 * @param int    $writer Output writer
	 */
	protected function ol(array $items, string $marker = '<yellow>%s</yellow>.', int $writer = Output::STANDARD): void
	{
		(new OrderedList($this->output))->draw($items, $marker, $writer);
	}

	/**
	 * Draws an unordered list.
	 *
	 * @param array  $items  Items
	 * @param string $marker Item marker
	 * @param int    $writer Output writer
	 */
	protected function ul(array $items, string $marker = '<yellow>*</yellow>', int $writer = Output::STANDARD): void
	{
		(new UnorderedList($this->output))->draw($items, $marker, $writer);
	}

	/**
	 * Writes question to output and returns boolesn value corresponding to the chosen value.
	 *
	 * @param  string $question Question to ask
	 * @param  string $default  Default answer
	 * @return bool
	 */
	protected function confirm(string $question, string $default = 'n')
	{
		return (new Confirmation($this->input, $this->output))->ask($question, $default);
	}

	/**
	 * Writes question to output and returns user input.
	 *
	 * @param  string $question Question to ask
	 * @param  mixed  $default  Default if no input is entered
	 * @return mixed
	 */
	protected function question(string $question, $default = null)
	{
		return (new Question($this->input, $this->output))->ask($question, $default);
	}

	/**
	 * Prints out a list of options and returns the array key of the chosen value.
	 *
	 * @param  string $question Question to ask
	 * @param  array  $options  Numeric array of options to choose from
	 * @return int
	 */
	protected function select(string $question, array $options): int
	{
		return (new Select($this->input, $this->output))->ask($question, $options);
	}

	/**
	 * Writes question to output and returns hidden user input.
	 *
	 * @param  string $question Question to ask
	 * @param  mixed  $default  Default if no input is entered
	 * @param  bool   $fallback Fall back to non-hidden input?
	 * @return mixed
	 */
	protected function secret(string $question, $default = null, bool $fallback = false)
	{
		return (new Secret($this->input, $this->output))->ask($question, $default, $fallback);
	}
}
