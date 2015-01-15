<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\error\handlers;

use Exception;

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
	 * @param   \Exception               $exception  Exception
	 * @param   \mako\cli\output\Output  $output     Output
	 */

	public function __construct(Exception $exception, Output $output)
	{
		parent::__construct($exception);

		$this->output = $output;
	}
	/**
	 * Returns a detailed error.
	 * 
	 * @access  protected
	 * @return  string
	 */

	protected function getDetailedError()
	{
		$this->output->errorLn('<bg_red><white>' . $this->exception->getMessage() . PHP_EOL . PHP_EOL . $this->exception->getTraceAsString() . PHP_EOL . '</white></bg_red>');
	}

	/**
	 * Retruns a generic error.
	 * 
	 * @access  protected
	 * @return  string
	 */

	protected function getGenericError()
	{
		$this->output->errorLn('<bg_red><white>An error has occurred while processing your task.</white></bg_red>' . PHP_EOL);
	}

	/**
	 * {@inheritdoc}
	 */

	public function handle($showDetails = true)
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