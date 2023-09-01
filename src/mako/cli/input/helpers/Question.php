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
	 */
	public function __construct(
		protected Input $input,
		protected Output $output
	)
	{}

	/**
	 * Writes question to output and returns user input.
	 */
	public function ask(string $question, mixed $default = null): mixed
	{
		$this->output->write(trim($question) . ' ');

		$answer = $this->input->read();

		return empty($answer) ? $default : $answer;
	}
}
