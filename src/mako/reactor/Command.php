<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\reactor;

use mako\cli\input\Input;
use mako\cli\input\helpers\Confirmation;
use mako\cli\input\helpers\Question;
use mako\cli\input\helpers\Secret;
use mako\cli\input\helpers\Select;
use mako\cli\output\Output;
use mako\cli\output\helpers\Bell;
use mako\cli\output\helpers\Countdown;
use mako\cli\output\helpers\OrderedList;
use mako\cli\output\helpers\ProgressBar;
use mako\cli\output\helpers\Table;
use mako\cli\output\helpers\UnorderedList;
use mako\syringe\traits\ContainerAwareTrait;

/**
 * Base command.
 *
 * @author Frederic G. Østby
 */
abstract class Command
{
	use ContainerAwareTrait;

	/**
	 * Success status code.
	 *
	 * @var int
	 */
	const STATUS_SUCCESS = 0;

	/**
	 * Error status code.
	 *
	 * @var int
	 */
	const STATUS_ERROR = 1;

	/**
	 * Input.
	 *
	 * @var \mako\cli\input\Input
	 */
	protected $input;

	/**
	 * Output.
	 *
	 * @var \mako\cli\output\Output
	 */
	protected $output;

	/**
	 * Command information.
	 *
	 * @var array
	 */
	protected $commandInformation =
	[
		'description' => '',
		'arguments'   => [],
		'options'     => [],
	];

	/**
	 * Should we be strict about what arguments and options we allow?
	 *
	 * @var bool
	 */
	protected $isStrict = false;

	/**
	 * Should the command be executed?
	 *
	 * @var bool
	 */
	protected $shouldExecute = true;

	/**
	 * Constructor.
	 *
	 * @param \mako\cli\input\Input   $input  Input
	 * @param \mako\cli\output\Output $output Output
	 */
	public function __construct(Input $input, Output $output)
	{
		$this->input = $input;

		$this->output = $output;

		if($this->input->getArgument('help') === true)
		{
			$this->displayCommandDetails();
		}
	}

	/**
	 * Returns the command description.
	 *
	 * @return string
	 */
	public function getCommandDescription(): string
	{
		return $this->commandInformation['description'] ?? '';
	}

	/**
	 * Returns the command arguments.
	 *
	 * @return array
	 */
	public function getCommandArguments(): array
	{
		return $this->commandInformation['arguments'] ?? [];
	}

	/**
	 * Returns the command options.
	 *
	 * @return array
	 */
	public function getCommandOptions(): array
	{
		return $this->commandInformation['options'] ?? [];
	}

	/**
	 * Returns TRUE we should be strict about what arguments and options we allow and FALSE if not.
	 *
	 * @return bool
	 */
	public function isStrict(): bool
	{
		return $this->isStrict;
	}

	/**
	 * Returns TRUE of the command should be executed and FALSE if not.
	 *
	 * @return bool
	 */
	public function shouldExecute(): bool
	{
		return $this->shouldExecute;
	}

	/**
	 * Draws an info table.
	 *
	 * @param array $items Items
	 */
	protected function drawInfoTable(array $items)
	{
		$headers = ['Name', 'Description', 'Optional'];

		$rows = [];

		foreach($items as $name => $argument)
		{
			$rows[] = [$name, $argument['description'], var_export($argument['optional'], true)];
		}

		$this->table($headers, $rows);
	}

	/**
	 * Displays command details.
	 */
	protected function displayCommandDetails()
	{
		$this->write('<yellow>Command:</yellow>');

		$this->nl();

		$this->write('php reactor ' . $this->input->getArgument(1));

		$this->nl();

		$this->write('<yellow>Description:</yellow>');

		$this->nl();

		$this->write($this->getCommandDescription());

		if(!empty($this->commandInformation['arguments']))
		{
			$this->nl();

			$this->write('<yellow>Arguments:</yellow>');

			$this->nl();

			$this->drawInfoTable($this->commandInformation['arguments']);
		}

		if(!empty($this->commandInformation['options']))
		{
			$this->nl();

			$this->write('<yellow>Options:</yellow>');

			$this->nl();

			$this->drawInfoTable($this->commandInformation['options']);
		}

		$this->shouldExecute = false;
	}

	/**
	 * Writes n newlines to output.
	 *
	 * @param int $lines  Number of newlines to write
	 * @param int $writer Output writer
	 */
	protected function nl(int $lines = 1, int $writer = Output::STANDARD)
	{
		$this->output->write(str_repeat(PHP_EOL, $lines), $writer);
	}

	/**
	 * Writes string to output.
	 *
	 * @param string $string String to write
	 * @param int    $writer Output writer
	 */
	protected function write(string $string, int $writer = Output::STANDARD)
	{
		$this->output->writeLn($string, $writer);
	}

	/**
	 * Writes string to output using the error writer.
	 *
	 * @param string $string String to write
	 */
	protected function error(string $string)
	{
		$this->output->errorLn('<red>' . $string . '</red>');
	}

	/**
	 * Clears the screen.
	 */
	protected function clear()
	{
		$this->output->clear();
	}

	/**
	 * Rings the terminal bell n times.
	 *
	 * @param int $times Number of times to ring the bell
	 */
	protected function bell(int $times = 1)
	{
		(new Bell($this->output))->ring($times);
	}

	/**
	 * Counts down from n.
	 *
	 * @param int $from Number of seconds to count down
	 */
	protected function countdown(int $from = 5)
	{
		(new Countdown($this->output))->draw($from);
	}

	/**
	 * Draws a progress bar and returns a progress bar instance.
	 *
	 * @param  int                                  $items      Total number of items
	 * @param  int|null                             $redrawRate Redraw rate
	 * @return \mako\cli\output\helpers\ProgressBar
	 */
	protected function progressBar(int $items, int $redrawRate = null): ProgressBar
	{
		$progressBar = new ProgressBar($this->output, $items, $redrawRate);

		$progressBar->setWidth(50);

		$progressBar->setEmptyTemplate('<red>░</red>');

		$progressBar->setFilledTemplate('<green>▓</green>');

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
	protected function table(array $columnNames, array $rows, int $writer = Output::STANDARD)
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
	protected function ol(array $items, string $marker = '<yellow>%s</yellow>.', int $writer = Output::STANDARD)
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
	protected function ul(array $items, string $marker = '<yellow>*</yellow>', int $writer = Output::STANDARD)
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
	 * @param  string     $question Question to ask
	 * @param  mixed|null $default  Default if no input is entered
	 * @return mixed|null
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
	 * @param  string     $question Question to ask
	 * @param  mixed|null $default  Default if no input is entered
	 * @param  bool       $fallback Fall back to non-hidden input?
	 * @return mixed|null
	 */
	protected function secret(string $question, $default = null, bool $fallback = false)
	{
		return (new Secret($this->input, $this->output))->ask($question, $default, $fallback);
	}
}
