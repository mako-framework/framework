<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\helpers;

use mako\cli\input\Input;
use mako\cli\output\Output;

use function array_values;
use function trim;

/**
 * Select helper.
 */
class Select
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
	 * Returns a list of options.
	 */
	protected function buildOptionsList(array $options): string
	{
		$output = '';

		foreach($options as $key => $option)
		{
			$output .= ($key + 1) . ') ' . $option . PHP_EOL;
		}

		return "{$output}> ";
	}

	/**
	 * Prints out a list of options and returns the array key of the chosen value.
	 */
	public function ask(string $question, array $options): int
	{
		$options = array_values($options);

		$this->output->writeLn(trim($question));

		$this->output->write($this->buildOptionsList($options));

		$key = (int) $this->input->read() - 1;

		if(isset($options[$key]))
		{
			return $key;
		}

		return $this->ask($question, $options);
	}
}
