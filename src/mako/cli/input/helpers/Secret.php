<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\helpers;

use RuntimeException;

use mako\cli\input\helpers\Question;
use mako\cli\input\Input;
use mako\cli\output\Output;

/**
 * Secret helper.
 *
 * @author Frederic G. Østby
 */
class Secret extends Question
{
	/**
	 * Do we have stty support?
	 *
	 * @var bool
	 */
	protected static $hasStty;

	/**
	 * Do we have stty support?
	 *
	 * @return bool
	 */
	protected function hasStty(): bool
	{
		if(static::$hasStty === null)
		{
			exec('stty 2>&1', $output, $status);

			static::$hasStty = $status === 0;
		}

		return static::$hasStty;
	}

	/**
	 * Writes question to output and returns hidden user input.
	 *
	 * @param  string $question Question to ask
	 * @param  mixed  $default  Default if no input is entered
	 * @param  bool   $fallback Fall back to non-hidden input?
	 * @return mixed
	 */
	public function ask(string $question, $default = null, bool $fallback = false)
	{
		if(DIRECTORY_SEPARATOR === '\\' || $this->hasStty())
		{
			$this->output->write(trim($question) . ' ');

			if(DIRECTORY_SEPARATOR === '\\')
			{
				$answer = trim(shell_exec(__DIR__ . '/resources/hiddeninput.exe'));
			}
			else
			{
				$settings = shell_exec('stty -g');

				exec('stty -echo');

				$answer = $this->input->read();

				exec('stty ' . $settings);
			}

			$this->output->write(PHP_EOL);

			return empty($answer) ? $default : $answer;
		}
		elseif($fallback)
		{
			return parent::ask($question, $default);
		}
		else
		{
			throw new RuntimeException('Unable to hide the user input.');
		}
	}
}
