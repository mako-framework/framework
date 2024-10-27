<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\helpers;

use mako\cli\output\helpers\spinner\Frames;
use mako\cli\output\Output;

use function count;
use function extension_loaded;
use function pcntl_fork;
use function pcntl_wait;
use function posix_getpid;
use function posix_kill;
use function sprintf;
use function usleep;

/**
 * Spinner.
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
		protected Frames $frames = new Frames,
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

		$frames = $this->frames->getFrames();

		$frameCount = count($frames);

		$timeBetweenRedraw = $this->frames->getTimeBetweenRedraw();

		while (true) {
			$this->output->write("\r" . sprintf($template, $frames[$i++ % $frameCount]) . " {$message}");

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
