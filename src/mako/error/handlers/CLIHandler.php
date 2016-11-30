<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\error\handlers;

use Throwable;

use mako\cli\output\Output;
use mako\error\handlers\Handler;

/**
 * CLI handler.
 *
 * @author  Frederic G. Østby
 */
class CLIHandler extends Handler
{
	/**
	 * Output.
	 *
	 * @var \mako\cli\output\Output
	 */
	protected $output;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \Throwable               $exception  Throwable
	 * @param   \mako\cli\output\Output  $output     Output
	 */
	public function __construct(Throwable $exception, Output $output)
	{
		parent::__construct($exception);

		$this->output = $output;
	}

	/**
	 * Escape formatting tags.
	 *
	 * @access  protected
	 * @param   string     $string  String to escape
	 * @return  string
	 */
	protected function escape(string $string): string
	{
		if(($formatter = $this->output->getFormatter()) === null)
		{
			return $string;
		}

		return $formatter->escape($string);
	}

	/**
	 * Returns a detailed error.
	 *
	 * @access  protected
	 * @return
	 */
	protected function getDetailedError()
	{
		$type = $this->escape($this->determineExceptionType($this->exception));

		$message = $this->escape($this->exception->getMessage());

		if(!empty($this->exception->getFile()))
		{
			$message .= PHP_EOL . PHP_EOL;
			$message .= 'Error location: ' . $this->escape($this->exception->getFile());
			$message .= ' on line ' . $this->escape($this->exception->getLine());
		}

		$trace = $this->escape($this->exception->getTraceAsString());

		$this->output->errorLn('<bg_red><white>' . $type . ': ' . $message . PHP_EOL . PHP_EOL . $trace . PHP_EOL . '</white></bg_red>');
	}

	/**
	 * Retruns a generic error.
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function getGenericError(): string
	{
		$this->output->errorLn('<bg_red><white>An error has occurred while processing your task.</white></bg_red>' . PHP_EOL);
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle(bool $showDetails = true)
	{
		// Set the response body

		if($showDetails)
		{
			$this->getDetailedError();
		}
		else
		{
			$this->getGenericError();
		}

		// Return false to stop further error handling

		return false;
	}
}
