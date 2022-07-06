<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\helpers;

use mako\cli\input\Input;
use mako\cli\output\Output;

use function trim;

/**
 * Question helper.
 */
class Question
{
	/**
	 * Constructor.
	 *
	 * @param \mako\cli\input\Input   $input  Input instance
	 * @param \mako\cli\output\Output $output Output instance
	 */
	public function __construct(
		protected Input $input,
		protected Output $output
	)
	{}

	/**
	 * Writes question to output and returns user input.
	 *
	 * @param  string $question Question to ask
	 * @param  mixed  $default  Default if no input is entered
	 * @return mixed
	 */
	public function ask(string $question, mixed $default = null): mixed
	{
		$this->output->write(trim($question) . ' ');

		$answer = $this->input->read();

		return empty($answer) ? $default : $answer;
	}
}
