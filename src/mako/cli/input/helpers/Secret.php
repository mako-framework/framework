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
	 * Writes question to output and returns hidden user input.
	 */
	public function ask(string $question, mixed $default = null, bool $fallback = false): mixed
	{
		$hasStty = $this->output->getEnvironment()->hasStty();

		if (PHP_OS_FAMILY === 'Windows' || $hasStty) {
			$this->displayPrompt($question);

			if ($hasStty) {
				$answer = $this->output->getEnvironment()->sttySandBox(function (): string {
					exec('stty -echo');
					return $this->input->read();
				});
			}
			else {
				$answer = trim(shell_exec(escapeshellcmd(__DIR__ . '/resources/hiddeninput.exe')));
			}

			$this->output->write(PHP_EOL);

			return empty($answer) ? $default : $answer;
		}

		if ($fallback) {
			return parent::ask($question, $default);
		}

		throw new CliException('Unable to hide the user input.');
	}
}
