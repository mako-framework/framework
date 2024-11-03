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
 */
class Question
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected Input $input,
		protected Output $output,
		protected string $prompt = '>'
	) {
	}

	/**
	 * Writes question to output.
	 */
	protected function displayPrompt(string $question): void
	{
		$this->output->write($question . PHP_EOL . "{$this->prompt} ");
	}

	/**
	 * Writes question to output and returns user input.
	 */
	public function ask(string $question, mixed $default = null): mixed
	{
		$this->displayPrompt($question);

		$answer = $this->input->read();

		return empty($answer) ? $default : $answer;
	}
}
