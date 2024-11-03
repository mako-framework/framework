<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\reactor\traits;

use Countable;
use mako\cli\input\helpers\Confirmation;
use mako\cli\input\helpers\Question;
use mako\cli\input\helpers\Secret;
use mako\cli\input\helpers\Select;
use mako\cli\output\components\Alert;
use mako\cli\output\components\Bell;
use mako\cli\output\components\Countdown;
use mako\cli\output\components\OrderedList;
use mako\cli\output\components\Progress;
use mako\cli\output\components\progress\ProgressBar;
use mako\cli\output\components\ProgressIterator;
use mako\cli\output\components\Spinner;
use mako\cli\output\components\spinner\Frames;
use mako\cli\output\components\Table;
use mako\cli\output\components\table\Border;
use mako\cli\output\components\UnorderedList;
use mako\cli\output\Output;
use mako\syringe\traits\ContainerAwareTrait;
use Traversable;

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
	 *
	 * @deprecated
	 */
	protected function progressBar(int $items, float $minTimeBetweenRedraw = 0.1, ?string $prefix = null): Progress
	{
		$progressBar = new Progress(
			$this->output,
			$items,
			description: $prefix ?? '',
			width: 50,
			minTimeBetweenRedraw: $minTimeBetweenRedraw
		);

		$progressBar->draw();

		return $progressBar;
	}

	/**
	 * Draws a progress bar and returns a progress instance.
	 */
	protected function progress(
		int $itemCount,
		string $description = '',
		int $width = 50,
		float $minTimeBetweenRedraw = 0.1,
		ProgressBar $progressBar = new ProgressBar('<red><faded>%s</faded></red>', '<green>%s</green>')
	): Progress {
		$progressBar = new Progress(
			$this->output,
			$itemCount,
			$description,
			$width,
			$minTimeBetweenRedraw,
			$progressBar
		);

		$progressBar->draw();

		return $progressBar;
	}

	/**
	 * Returns a progress iterator instance.
	 */
	protected function progressIterator(
		array|(Countable&Traversable) $items,
		string $description = '',
		int $width = 50,
		float $minTimeBetweenRedraw = 0.1,
		ProgressBar $progressBar = new ProgressBar('<red><faded>%s</faded></red>', '<green>%s</green>')
	): ProgressIterator {
		return new ProgressIterator(
			$this->output,
			$items,
			$description,
			$width,
			$minTimeBetweenRedraw,
			$progressBar
		);
	}

	/**
	 * Draws a spinner while executing the callback.
	 */
	protected function spinner(string $message, callable $callback, Frames $frames = new Frames('<green>%s</green>')): void
	{
		(new Spinner($this->output, $frames))->spin($message, $callback);
	}

	/**
	 * Draws a table.
	 */
	protected function table(array $columnNames, array $rows, int $writer = Output::STANDARD, Border $borderStyle = new Border): void
	{
		(new Table($this->output, $borderStyle))->draw($columnNames, $rows, $writer);
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
	protected function confirm(string $question, string $default = 'n', string $prompt = '<purple><bold>></bold></purple>'): bool
	{
		return (new Confirmation($this->input, $this->output, $prompt))->ask($question, $default);
	}

	/**
	 * Writes question to output and returns user input.
	 */
	protected function question(string $question, mixed $default = null, string $prompt = '<purple><bold>></bold></purple>'): mixed
	{
		return (new Question($this->input, $this->output, $prompt))->ask($question, $default);
	}

	/**
	 * Prints out a list of options and returns the array key of the chosen value.
	 */
	protected function select(string $question, array $options, string $prompt = '<purple><bold>></bold></purple>'): int
	{
		return (new Select($this->input, $this->output, $prompt))->ask($question, $options);
	}

	/**
	 * Writes question to output and returns hidden user input.
	 */
	protected function secret(string $question, mixed $default = null, bool $fallback = false, string $prompt = '<purple><bold>></bold></purple>'): mixed
	{
		return (new Secret($this->input, $this->output, $prompt))->ask($question, $default, $fallback);
	}
}
