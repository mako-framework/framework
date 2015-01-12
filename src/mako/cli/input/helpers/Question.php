<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cli\input\helpers;

/**
 * Question helper.
 *
 * @author  Frederic G. Østby
 */

use mako\cli\input\Input;
use mako\cli\output\Output;

class Question
{
	/**
	 * Input instance.
	 * 
	 * @var \mako\cli\input\Input
	 */

	protected $input;

	/**
	 * Output instance.
	 * 
	 * @var \mako\cli\output\Output
	 */

	protected $ouput;

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\cli\input\Input    $input   Input instance
	 * @param   \mako\cli\output\Output  $output  Output instance
	 */

	public function __construct(Input $input, Output $output)
	{
		$this->input = $input;

		$this->output = $output;
	}

	/**
	 * Writes question to output and returns user input.
	 * 
	 * @access  public
	 * @param   string  $question  Question to ask
	 * @return  string
	 */

	public function ask($question)
	{
		$this->output->write(trim($question) . ' ');

		return $this->input->read();
	}
}