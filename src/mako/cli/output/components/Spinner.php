<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\components;

use mako\cli\output\components\spinner\Theme;
use mako\cli\output\Output;

use function count;
use function extension_loaded;
use function pcntl_fork;
use function pcntl_wait;
use function posix_getpid;
use function posix_getppid;
use function posix_kill;
use function usleep;

/**
 * Spinner component.
 */
class Spinner
{
	/**
	 * Can we fork the process?
	 */
	protected bool $canFork;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Output $output,
		protected Theme $theme = new Theme,
	) {
		$this->canFork = $this->canFork();
	}

	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		$this->output->restoreCursor();
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
	protected function spinner(string $message): void
	{
		$i = 0;

		$frames = $this->theme->getFrames();

		$frameCount = count($frames);

		$timeBetweenRedraw = $this->theme->getTimeBetweenRedraw();

		while (true) {
			$this->output->write("\r" . $frames[$i++ % $frameCount] . " {$message}");

			if (posix_kill(posix_getpid(), 0) === false) {
				break;
			}

			if (posix_getppid() === 1) {
				posix_kill(posix_getpid(), SIGKILL);
			}

			usleep($timeBetweenRedraw);
		}
	}

	/**
	 * Draws the spinner.
	 */
	public function spin(string $message, callable $callback): mixed
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

			$this->spinner($message);
		}

		$this->output->showCursor();

		return $result;
	}
}
