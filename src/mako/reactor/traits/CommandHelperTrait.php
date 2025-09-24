<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\reactor\traits;

use Countable;
use Deprecated;
use mako\cli\input\helpers\Confirmation;
use mako\cli\input\helpers\confirmation\Theme as ConfirmationTheme;
use mako\cli\input\helpers\Prompt;
use mako\cli\input\helpers\Secret;
use mako\cli\input\helpers\Select;
use mako\cli\input\helpers\select\Theme as SelectTheme;
use mako\cli\output\components\Alert;
use mako\cli\output\components\Bell;
use mako\cli\output\components\Countdown;
use mako\cli\output\components\LabelsAndValues;
use mako\cli\output\components\OrderedList;
use mako\cli\output\components\Progress;
use mako\cli\output\components\progress\Theme as ProgressTheme;
use mako\cli\output\components\ProgressIterator;
use mako\cli\output\components\Spinner;
use mako\cli\output\components\spinner\Theme as SpinnerTheme;
use mako\cli\output\components\Table;
use mako\cli\output\components\table\Theme as TableTheme;
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
	 * Draws a progress bar and returns a progress instance.
	 */
	protected function progress(
		int $itemCount,
		string $description = '',
		int $width = 50,
		float $minTimeBetweenRedraw = 0.1,
		ProgressTheme $theme = new ProgressTheme('<red><faded>%s</faded></red>', '<green>%s</green>')
	): Progress {
		$progressBar = new Progress(
			$this->output,
			$itemCount,
			$description,
			$width,
			$minTimeBetweenRedraw,
			$theme
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
		ProgressTheme $theme = new ProgressTheme('<red><faded>%s</faded></red>', '<green>%s</green>')
	): ProgressIterator {
		return new ProgressIterator(
			$this->output,
			$items,
			$description,
			$width,
			$minTimeBetweenRedraw,
			$theme
		);
	}

	/**
	 * Draws a spinner while executing the callback.
	 */
	protected function spinner(string $message, callable $callback, SpinnerTheme $theme = new SpinnerTheme('<green>%s</green>')): mixed
	{
		return (new Spinner($this->output, $theme))->spin($message, $callback);
	}

	/**
	 * Draws a table.
	 */
	protected function table(array $columnNames, array $rows, int $writer = Output::STANDARD, TableTheme $theme = new TableTheme): void
	{
		(new Table($this->output, $theme))->draw($columnNames, $rows, $writer);
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
	 * Asks the user for confirmation.
	 */
	protected function confirm(
		string $question,
		bool $default = false,
		string $trueLabel = 'Yes',
		string $falseLabel = 'No',
		ConfirmationTheme $theme = new ConfirmationTheme('<green>%s</green>', '<red>%s</red>', '<purple><bold>%s</bold></purple>')
	): bool {
		return (new Confirmation(
			$this->input,
			$this->output,
			$trueLabel,
			$falseLabel,
			$theme
		))->ask($question, $default);
	}

	/**
	 * Prompts the user for input and returns the user input.
	 */
	protected function input(string $prompt, mixed $default = null, string $inputPrefix = '<purple><bold>></bold></purple>'): mixed
	{
		return (new Prompt($this->input, $this->output, $inputPrefix))->ask($prompt, $default);
	}

	/**
	 * Prompts the user for input and returns the user input.
	 */
	#[Deprecated('use the "input" method instead', since: 'Mako 11.2.0')]
	protected function question(string $question, mixed $default = null, string $prompt = '<purple><bold>></bold></purple>'): mixed
	{
		return $this->input($question, $default, $prompt);
	}

	/**
	 * Prints out a list of options and returns the chosen option(s).
	 */
	protected function select(
		string $label,
		array $options,
		string $invalidChoiceMessage = '<red>Invalid choice. Please try again.</red>',
		string $choiceRequiredMessage = '<red>You need to make a selection.</red>',
		SelectTheme $theme = new SelectTheme('<purple><bold>%s</bold></purple>'),
		bool $returnKey = true,
		bool $allowMultiple = false,
		bool $allowEmptySelection = false,
		?callable $optionFormatter = null
	): mixed {
		return (new Select(
			$this->input,
			$this->output,
			$invalidChoiceMessage,
			$choiceRequiredMessage,
			$theme,
			$returnKey,
			$allowMultiple,
			$allowEmptySelection
		))->ask($label, $options, $optionFormatter);
	}

	/**
	 * Writes question to output and returns hidden user input.
	 */
	protected function secret(
		string $question,
		mixed $default = null,
		string $inputPrefix = '<purple><bold>></bold></purple>',
		bool $fallback = false
	): mixed {
		return (new Secret(
			$this->input,
			$this->output,
			$inputPrefix,
			$fallback
		))->ask($question, $default);
	}

	/**
	 * Draws labels and values.
	 */
	protected function labelsAndValues(
		array $labelsAndValues,
		float|int $widthPercent = 0,
		int $maxWidth = PHP_INT_MAX,
		int $minSeparatorCount = 5,
		string $separatorTemplate = '<faded>%s</faded>'
	): void {
		(new LabelsAndValues(
			$this->output,
			$widthPercent,
			$maxWidth,
			$minSeparatorCount,
			$separatorTemplate)
		)->draw($labelsAndValues);
	}
}
