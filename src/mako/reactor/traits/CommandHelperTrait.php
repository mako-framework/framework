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
use mako\cli\output\helpers\Alert;
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
 */
trait CommandHelperTrait
{
	use ContainerAwareTrait;

	/**
	 * Writes n newlines to output.
	 */
	protected function nl(int $lines = 1, int $writer = Output::STANDARD): void
	{
		$this->output->write(str_repeat(PHP_EOL, $lines), $writer);
	}

	/**
	 * Writes string to output.
	 */
	protected function write(string $string, int $writer = Output::STANDARD): void
	{
		$this->output->writeLn($string, $writer);
	}

	/**
	 * Writes string to output using the error writer.
	 */
	protected function error(string $string): void
	{
		$this->output->errorLn("<red>{$string}</red>");
	}

	/**
	 * Clears the screen.
	 */
	protected function clear(): void
	{
		$this->output->clear();
	}

	/**
	 * Draws an alert.
	 */
	protected function alert(string $message, string $template = Alert::DEFAULT, int $writer = Output::STANDARD): void
	{
		(new Alert($this->output))->draw($message, $template, $writer);
	}

	/**
	 * Rings the terminal bell n times.
	 */
	protected function bell(int $times = 1): void
	{
		(new Bell($this->output))->ring($times);
	}

	/**
	 * Counts down from n.
	 */
	protected function countdown(int $from = 5): void
	{
		(new Countdown($this->output))->draw($from);
	}

	/**
	 * Draws a progress bar and returns a progress bar instance.
	 */
	protected function progressBar(int $items, float $minTimeBetweenRedraw = 0.1, ?string $prefix = null): ProgressBar
	{
		$progressBar = new ProgressBar($this->output, $items, $minTimeBetweenRedraw);

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
	 */
	protected function table(array $columnNames, array $rows, int $writer = Output::STANDARD): void
	{
		(new Table($this->output))->draw($columnNames, $rows, $writer);
	}

	/**
	 * Draws an ordered list.
	 */
	protected function ol(array $items, string $marker = '<yellow>%s</yellow>.', int $writer = Output::STANDARD): void
	{
		(new OrderedList($this->output))->draw($items, $marker, $writer);
	}

	/**
	 * Draws an unordered list.
	 */
	protected function ul(array $items, string $marker = '<yellow>*</yellow>', int $writer = Output::STANDARD): void
	{
		(new UnorderedList($this->output))->draw($items, $marker, $writer);
	}

	/**
	 * Writes question to output and returns boolesn value corresponding to the chosen value.
	 */
	protected function confirm(string $question, string $default = 'n')
	{
		return (new Confirmation($this->input, $this->output))->ask($question, $default);
	}

	/**
	 * Writes question to output and returns user input.
	 */
	protected function question(string $question, mixed $default = null): mixed
	{
		return (new Question($this->input, $this->output))->ask($question, $default);
	}

	/**
	 * Prints out a list of options and returns the array key of the chosen value.
	 */
	protected function select(string $question, array $options): int
	{
		return (new Select($this->input, $this->output))->ask($question, $options);
	}

	/**
	 * Writes question to output and returns hidden user input.
	 */
	protected function secret(string $question, mixed $default = null, bool $fallback = false): mixed
	{
		return (new Secret($this->input, $this->output))->ask($question, $default, $fallback);
	}
}
