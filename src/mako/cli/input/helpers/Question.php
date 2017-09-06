<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\helpers;

use mako\cli\input\Input;
use mako\cli\output\Output;

/**
 * Question helper.
 *
 * @author Frederic G. Østby
 */
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
	 * @param \mako\cli\input\Input   $input  Input instance
	 * @param \mako\cli\output\Output $output Output instance
	 */
	public function __construct(Input $input, Output $output)
	{
		$this->input = $input;

		$this->output = $output;
	}

	/**
	 * Writes question to output and returns user input.
	 *
	 * @param  string     $question Question to ask
	 * @param  mixed|null $default  Default if no input is entered
	 * @return mixed|null
	 */
	public function ask(string $question, $default = null)
	{
		$this->output->write(trim($question) . ' ');

		$answer = $this->input->read();

		return empty($answer) ? $default : $answer;
	}
}
