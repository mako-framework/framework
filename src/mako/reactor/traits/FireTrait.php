<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\reactor\traits;

use Closure;

use function escapeshellarg;
use function escapeshellcmd;
use function feof;
use function fread;
use function pclose;
use function popen;
use function str_contains;

/**
 * Fire trait.
 *
 * @property \mako\application\cli\Application $app
 */
trait FireTrait
{
	/**
	 * Returns path to the reactor executable.
	 */
	protected function buildReactorPath(): string
	{
		return $this->app->getPath() . DIRECTORY_SEPARATOR . 'reactor';
	}

	/**
	 * Returns command that we're going to execute.
	 */
	protected function buildCommand(string $command, bool $background = false, bool $sameEnvironment = true): string
	{
		if ($sameEnvironment && str_contains($command, '--env=') === false && ($environment = $this->app->getEnvironment()) !== null) {
			$command .= ' --env=' . escapeshellarg($environment);
		}

		$command = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg($this->buildReactorPath()) . " {$command} 2>&1";

		if (PHP_OS_FAMILY === 'Windows') {
			if ($background) {
				$command = "/b {$command}";
			}

			return "start {$command}";
		}

		if ($background) {
			$command .= ' &';
		}

		return $command;
	}

	/**
	 * Runs command as a separate process and feeds output to handler.
	 */
	protected function fire(string $command, ?Closure $handler = null, bool $sameEnvironment = true): int
	{
		$process = popen($this->buildCommand($command, sameEnvironment: $sameEnvironment), 'r');

		while (!feof($process)) {
			$read = fread($process, 4096);

			if ($handler !== null) {
				$handler($read);
			}
		}

		return pclose($process);
	}

	/**
	 * Starts command as a background process.
	 */
	protected function fireAndForget(string $command, bool $sameEnvironment = true): void
	{
		pclose(popen($this->buildCommand($command, true, $sameEnvironment), 'r'));
	}
}
