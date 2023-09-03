<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli;

use function current;
use function exec;
use function getenv;
use function preg_match;

/**
 * Environment.
 */
class Environment
{
	/**
	 * Default width.
	 *
	 * @var int
	 */
	public const DEFAULT_WIDTH = 80;

	/**
	 * Default height.
	 *
	 * @var int
	 */
	public const DEFAULT_HEIGHT = 25;

	/**
	 * Do we have ANSI support?
	 */
	protected null|bool $hasAnsiSupport = null;

	/**
	 * Attempts to get dimensions for Windows.
	 */
	protected function getDimensionsForWindows(): ?array
	{
		return null;
	}

	/**
	 * Attempts to get dimensions for Unix-like platforms.
	 */
	protected function getDimensionsForUnixLike(): ?array
	{
		// Attempt to get dimensions from environment

		if(($width = getenv('COLUMNS')) !== false && ($height = getenv('LINES')) !== false)
		{
			return ['width' => (int) $width, 'height' => (int) $height];
		}

		// Attempt to get dimensions from stty

		exec('stty size', $output, $status);

		if($status === 0 && preg_match('/^([0-9]+) ([0-9]+)$/', current($output), $matches) === 1)
		{
			return ['width' => (int) $matches[2], 'height' => (int) $matches[1]];
		}

		// All attempts failed so we'll just return null

		return null;
	}

	/**
	 * Returns the console dimensions (width & height).
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

	/**
	 * Do we have ANSI support?
	 */
	public function hasAnsiSupport(): bool
	{
		if($this->hasAnsiSupport === null)
		{
			$this->hasAnsiSupport = PHP_OS_FAMILY !== 'Windows' || (getenv('ANSICON') !== false || getenv('ConEmuANSI') === 'ON');
		}

		return $this->hasAnsiSupport;
	}
}
