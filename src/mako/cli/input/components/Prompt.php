<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\components;

use mako\cli\input\Input;
use mako\cli\output\Output;

/**
 * Prompt component.
 */
class Prompt
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected Input $input,
		protected Output $output,
		protected string $inputPrefix = '>'
	) {
	}

	/**
	 * Writes prompt to output.
	 */
	protected function displayPrompt(string $prompt): void
	{
		$this->output->write($prompt . PHP_EOL . "{$this->inputPrefix} ");
	}

	/**
	 * Writes prompt to output and returns user input.
	 */
	public function ask(string $prompt, mixed $default = null): mixed
	{
		$this->displayPrompt($prompt);

		$answer = $this->input->read();

		return empty($answer) ? $default : $answer;
	}
}
