<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\reactor\traits;

use Closure;

use function feof;
use function fread;
use function pclose;
use function popen;
use function strpos;

/**
 * Fire trait.
 *
 * @author Frederic G. Østby
 */
trait FireTrait
{
	/**
	 * Returns path to the reactor executable.
	 *
	 * @return string
	 */
	protected function buildReactorPath(): string
	{
		return $this->app->getPath() . DIRECTORY_SEPARATOR . 'reactor';
	}

	/**
	 * Returns command that we're going to execute.
	 *
	 * @param  string $command         Command
	 * @param  bool   $background      Is it a background process?
	 * @param  bool   $sameEnvironment Run command using the same environment?
	 * @return string
	 */
	protected function buildCommand(string $command, bool $background = false, bool $sameEnvironment = true): string
	{
		if($sameEnvironment && strpos($command, '--env=') === false && ($environment = $this->app->getEnvironment()) !== null)
		{
			$command .= ' ' . escapeshellarg("--env={$environment}");
		}

		$command = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg($this->buildReactorPath()) . " {$command} 2>&1";

		if(DIRECTORY_SEPARATOR === '\\')
		{
			if($background)
			{
				$command = "/b {$command}";
			}

			return "start {$command}";
		}

		if($background)
		{
			$command .= ' &';
		}

		return $command;
	}

	/**
	 * Runs command as a separate process and feeds output to handler.
	 *
	 * @param  string        $command         Command
	 * @param  \Closure|null $handler         Output handler
	 * @param  bool          $sameEnvironment Run command using the same environment?
	 * @return int
	 */
	protected function fire(string $command, ?Closure $handler = null, bool $sameEnvironment = true): int
	{
		$process = popen($this->buildCommand($command, false, $sameEnvironment), 'r');

		while(!feof($process))
		{
			$read = fread($process, 4096);

			if($handler !== null)
			{
				$handler($read);
			}
		}

		return pclose($process);
	}

	/**
	 * Starts command as a background process.
	 *
	 * @param string $command         Command
	 * @param bool   $sameEnvironment Run command using the same environment?
	 */
	protected function fireAndForget(string $command, bool $sameEnvironment = true): void
	{
		pclose(popen($this->buildCommand($command, true, $sameEnvironment), 'r'));
	}
}
