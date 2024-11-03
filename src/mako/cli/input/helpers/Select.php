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
		protected Output $output,
		protected string $prompt = '>'
	) {
	}

	/**
	 * Returns a list of options.
	 */
	protected function buildOptionsList(array $options): string
	{
		$output = '';

		foreach ($options as $key => $option) {
			$output .= ($key + 1) . ') ' . $option . PHP_EOL;
		}

		return "{$output}{$this->prompt} ";
	}

	/**
	 * Prints out a list of options and returns the array key of the chosen value.
	 */
	public function ask(string $question, array $options): int
	{
		$options = array_values($options);

		$this->output->writeLn(trim($question));

		$this->output->write($this->buildOptionsList($options));

		$input = $this->input->read();

		if (is_numeric($input)) {

			$key = (int) $input - 1;

			if (isset($options[$key])) {
				return $key;
			}
		}
		else {
			$key = array_search(mb_strtolower($input), array_map(fn ($value) => mb_strtolower($value), $options));

			if ($key !== false) {
				return $key;
			}
		}

		return $this->ask($question, $options);
	}
}
