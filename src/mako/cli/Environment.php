<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli;

use function current;
use function exec;
use function mako\env;
use function preg_match;
use function shell_exec;

/**
 * Environment.
 */
class Environment
{
	/**
	 * Default width.
	 */
	public const int DEFAULT_WIDTH = 80;

	/**
	 * Default height.
	 */
	public const int DEFAULT_HEIGHT = 25;

	/**
	 * Do we have ANSI support?
	 */
	protected null|bool $hasAnsiSupport = null;

	/**
	 * Do we have stty support?
	 */
	protected null|bool $hasStty = null;

	/**
	 * Stty settings.
	 */
	protected ?string $sttySettings = null;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		if ($this->hasStty()) {
			$this->sttySettings = shell_exec('stty -g');
		}
	}

	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		$this->restoreStty();
	}

	/**
	 * Do we have ANSI support?
	 */
	public function hasAnsiSupport(): bool
	{
		if ($this->hasAnsiSupport === null) {
			$this->hasAnsiSupport = PHP_OS_FAMILY !== 'Windows' || (env('ANSICON') !== null || env('ConEmuANSI') === 'ON');
		}

		return $this->hasAnsiSupport;
	}

	/**
	 * Do we have stty support?
	 */
	public function hasStty(): bool
	{
		if ($this->hasStty === null) {
			exec('stty 2>&1', $output, $status);

			$this->hasStty = $status === 0;
		}

		return $this->hasStty;
	}

	/**
	 * Restores the stty settings.
	 */
	public function restoreStty(): void
	{
		if ($this->sttySettings !== null) {
			exec("stty {$this->sttySettings}");
		}
	}

	/**
	 * Executes a callable in a stty sandbox.
	 */
	public function sttySandbox(callable $callable): mixed
	{
		$settings = shell_exec('stty -g');

		try {
			return $callable();
		}
		finally {
			exec("stty {$settings}");
		}
	}

	/**
	 * Attempts to get dimensions for Windows.
	 */
	protected function getDimensionsForWindows(): ?array
	{
		return null;
	}

	/**
	 * Attempts to get dimensions for Unix-like platforms.
	 *
	 * @return array{width: int, height: int}|null
	 */
	protected function getDimensionsForUnixLike(): ?array
	{
		// Attempt to get dimensions from environment

		if (($width = env('COLUMNS')) !== null && ($height = env('LINES')) !== null) {
			return ['width' => (int) $width, 'height' => (int) $height];
		}

		// Attempt to get dimensions from stty

		exec('stty size', $output, $status);

		if ($status === 0 && preg_match('/^([0-9]+) ([0-9]+)$/', current($output), $matches) === 1) {
			return ['width' => (int) $matches[2], 'height' => (int) $matches[1]];
		}

		// All attempts failed so we'll just return null

		return null;
	}

	/**
	 * Returns the console dimensions (width & height).
	 *
	 * @return array{width: int, height: int}
	 */
	public function getDimensions(): array
	{
		$dimensions = PHP_OS_FAMILY === 'Windows' ? $this->getDimensionsForWindows() : $this->getDimensionsForUnixLike();

		return $dimensions ?? ['width' => static::DEFAULT_WIDTH, 'height' => static::DEFAULT_HEIGHT];
	}

	/**
	 * Returns the console width.
	 */
	public function getWidth(): int
	{
		return $this->getDimensions()['width'];
	}

	/**
	 * Returns the console height.
	 */
	public function getHeight(): int
	{
		return $this->getDimensions()['height'];
	}
}
