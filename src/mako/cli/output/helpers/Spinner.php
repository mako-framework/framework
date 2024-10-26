<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\helpers;

use mako\cli\output\Output;

use function count;
use function extension_loaded;
use function pcntl_fork;
use function pcntl_wait;
use function posix_getpid;
use function posix_kill;
use function usleep;

/**
 * Spinner.
 */
class Spinner
{
	/**
	 * Spinner frames.
	 */
	public const array FRAMES = ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'];

	/**
	 * Time between redraw in microseconds.
	 */
	protected const int TIME_BETWEEN_REDRAW = 100000;

	/**
	 * Can we fork the process?
	 */
	protected bool $canFork;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Output $output,
		protected array $frames = Spinner::FRAMES,
	) {
		$this->canFork = $this->canFork();
	}

	/**
	 * Returns TRUE if we can fork the process and FALSE if not.
	 */
	protected function canFork(): bool
	{
		return extension_loaded('pcntl') && extension_loaded('posix');
	}

	/**
	 * Draws the spinner.
	 */
	protected function spinner(string $message, string $template): void
	{
		$i = 0;

		$frames = count($this->frames);

		while (true) {
			$this->output->write("\r" . sprintf($template, $this->frames[$i++ % $frames]) . " {$message}");

			usleep(static::TIME_BETWEEN_REDRAW);

			if (posix_kill(posix_getpid(), 0) === false) {
				break;
			}
		}
	}

	/**
	 * Draws the spinner.
	 */
	public function spin(string $message, callable $callback, string $template = '%s'): mixed
	{
		$result = null;

		$this->output->hideCursor();

		$pid = $this->canFork ? pcntl_fork() : -1;

		if ($pid == -1) {
			// We were unable to fork the process so we'll just run the callback in the current process

			$this->output->write($message);

			$result = $callback();

			$this->output->clearLine();
		}
		elseif ($pid) {
			// We're in the parent process so we'll execute the callback here

			$result = $callback();

			posix_kill($pid, SIGKILL);

			pcntl_wait($status);

			$this->output->clearLine();
		}
		else {
			// We're in the child process so we'll display the spinner

			$this->spinner($message, $template);
		}

		$this->output->showCursor();

		return $result;
	}
}
