<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\helpers;

use mako\cli\exceptions\CliException;

use function escapeshellcmd;
use function exec;
use function shell_exec;
use function trim;

/**
 * Secret helper.
 */
class Secret extends Question
{
	/**
	 * Do we have stty support?
	 */
	protected static bool $hasStty;

	/**
	 * Do we have stty support?
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
	 */
	public function ask(string $question, mixed $default = null, bool $fallback = false): mixed
	{
		if(PHP_OS_FAMILY === 'Windows' || $this->hasStty())
		{
			$this->output->write(trim($question) . ' ');

			if(PHP_OS_FAMILY === 'Windows')
			{
				$answer = trim(shell_exec(escapeshellcmd(__DIR__ . '/resources/hiddeninput.exe')));
			}
			else
			{
				$settings = shell_exec('stty -g');

				exec('stty -echo');

				$answer = $this->input->read();

				exec("stty {$settings}");
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
			throw new CliException('Unable to hide the user input.');
		}
	}
}
