<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\reactor;

use mako\cli\input\Input;
use mako\cli\input\helpers\Confirmation;
use mako\cli\input\helpers\Question;
use mako\cli\input\helpers\Secret;
use mako\cli\output\Output;
use mako\cli\output\helpers\Bell;
use mako\cli\output\helpers\Countdown;
use mako\cli\output\helpers\Table;
use mako\cli\output\helpers\ProgressBar;
use mako\syringe\ContainerAwareTrait;

/**
 * Base command.
 *
 * @author  Frederic G. Østby
 */

abstract class Command
{
	use ContainerAwareTrait;

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
	 * Should the command be executed?
	 *
	 * @var boolean
	 */

	protected $shouldExecute = true;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\cli\input\Input    $input   Input
	 * @param   \mako\cli\output\Output  $output  Output
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
	 * @access  public
	 * @return  string|null
	 */

	public function getCommandDescription()
	{
		return isset($this->commandInformation['description']) ? $this->commandInformation['description'] : null;
	}

	/**
	 * Returns TRUE of the command should be executed and FALSE if not.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function shouldExecute()
	{
		return $this->shouldExecute;
	}

	/**
	 * Draws an info table.
	 *
	 * @access  protected
	 * @param   array      $items  Items
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
	 *
	 * @access  protected
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
	 * @access  protected
	 * @param   int        $lines   Number of newlines to write
	 * @param   int        $writer  Output writer
	 */

	protected function nl($lines = 1, $writer = Output::STANDARD)
	{
		$this->output->write(str_repeat(PHP_EOL, $lines), $writer);
	}

	/**
	 * Writes string to output.
	 *
	 * @access  protected
	 * @param   string     $string  String to write
	 * @param   int        $writer  Output writer
	 */

	protected function write($string, $writer = Output::STANDARD)
	{
		$this->output->writeLn($string, $writer);
	}

	/**
	 * Writes string to output using the error writer.
	 *
	 * @access  protected
	 * @param   string     $string  String to write
	 */

	protected function error($string)
	{
		$this->output->errorLn('<red>' . $string . '</red>');
	}

	/**
	 * Rings the terminal bell n times.
	 *
	 * @access  protected
	 * @param   int        $times  Number of times to ring the bell
	 */

	protected function bell($times = 1)
	{
		(new Bell($this->output))->ring($times);
	}

	/**
	 * Counts down from n.
	 *
	 * @access  protected
	 * @param   int        $from  Number of seconds to count down
	 */

	protected function countdown($from = 5)
	{
		(new Countdown($this->output))->draw($from);
	}

	/**
	 * Draws a progress bar and returns a progress bar instance.
	 *
	 * @access  protected
	 * @param   int                                  $items       Total number of items
	 * @param   int                                  $redrawRate  Redraw rate
	 * @return  \mako\cli\output\helpers\ProgessBar
	 */

	protected function progressBar($items, $redrawRate = null)
	{
		$progressBar = new ProgressBar($this->output, $items, $redrawRate);

		$progressBar->setEmptyTemplate('<red>-</red>');

		$progressBar->setFilledTemplate('<green>=</green>');

		$progressBar->draw();

		return $progressBar;
	}

	/**
	 * Draws a table.
	 *
	 * @access  protected
	 * @param   array      $columnNames  Array of column names
	 * @param   array      $rows         Array of rows
	 * @param   int        $writer       Output writer
	 */

	protected function table(array $columnNames, array $rows, $writer = Output::STANDARD)
	{
		(new Table($this->output))->draw($columnNames, $rows, $writer);
	}

	/**
	 * Writes question to output and returns boolesn value corresponding to the chosen value.
	 *
	 * @access  protected
	 * @param   string     $question  Question to ask
	 * @param   string     $default   Default answer
	 * @return  boolean
	 */

	protected function confirm($question, $default = 'n')
	{
		return (new Confirmation($this->input, $this->output))->ask($question, $default);
	}

	/**
	 * Writes question to output and returns user input.
	 *
	 * @access  protected
	 * @param   string      $question  Question to ask
	 * @param   null|mixed  $default   Default if no input is entered
	 * @return  string
	 */

	public function question($question, $default = null)
	{
		return (new Question($this->input, $this->output))->ask($question, $default);
	}

	/**
	 * Writes question to output and returns hidden user input.
	 *
	 * @access  protected
	 * @param   string      $question  Question to ask
	 * @param   null|mixed  $default   Default if no input is entered
	 * @param   boolean     $fallback  Fall back to non-hidden input?
	 * @return  string
	 */

	public function secret($question, $default = null, $fallback = false)
	{
		return (new Secret($this->input, $this->output))->ask($question, $default, $fallback);
	}
}