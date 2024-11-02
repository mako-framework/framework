<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\components\progress;

/**
 * Progress bar.
 */
class ProgressBar
{
	/**
	 * Empty progress bar character.
	 */
	protected const string EMPTY = '─';

	/**
	 * Filled progress bar character.
	 */
	protected const string FILLED = '█';

	/**
	 * Returns the empty progress bar character.
	 */
	public function getEmpty(): string
	{
		return static::EMPTY;
	}

	/**
	 * Returns the filled progress bar character.
	 */
	public function getFilled(): string
	{
		return static::FILLED;
	}
}
