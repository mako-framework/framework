<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\helpers;

use mako\cli\exceptions\CliException;
use mako\cli\input\Input;
use mako\cli\output\Output;
use mako\cli\traits\SttyTrait;
use Override;

use function escapeshellcmd;
use function shell_exec;
use function trim;

/**
 * Secret helper.
 */
class Secret extends Prompt
{
	use SttyTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		Input $input,
		Output $output,
		string $inputPrefix = '>',
		protected bool $fallback = false
	) {
		parent::__construct($input, $output, $inputPrefix);
	}

	/**
	 * Writes prompt to output and returns user input.
	 */
	#[Override]
	public function ask(string $prompt, mixed $default = null): mixed
	{
		$hasStty = $this->output->environment->hasStty();

		if (PHP_OS_FAMILY === 'Windows' || $hasStty) {
			$this->displayPrompt($prompt);

			if ($hasStty) {
				$answer = $this->sttySandbox(function (): string {
					$this->setSttySettings('-echo');
					return $this->input->read();
				});
			}
			else {
				$answer = trim(shell_exec(escapeshellcmd(__DIR__ . '/secret/hiddeninput.exe')));
			}

			$this->output->write(PHP_EOL);

			return empty($answer) ? $default : $answer;
		}

		if ($this->fallback) {
			return parent::ask($prompt, $default);
		}

		throw new CliException('Unable to hide the user input.');
	}
}
