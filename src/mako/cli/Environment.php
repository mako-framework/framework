<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli;

use mako\cli\traits\SttyTrait;

use function current;
use function exec;
use function mako\env;
use function preg_match;

/**
 * Environment.
 */
class Environment
{
	use SttyTrait {
		hasStty as public;
	}

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
	protected ?bool $hasAnsiSupport = null;

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
			$this->sttySettings = $this->getSttySettings();
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
	 * Restores the stty settings.
	 */
	public function restoreStty(): void
	{
		if ($this->sttySettings !== null) {
			$this->setSttySettings($this->sttySettings);
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
