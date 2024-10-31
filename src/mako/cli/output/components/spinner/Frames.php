<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\components\spinner;

/**
 * Frames.
 */
class Frames
{
	/**
	 * Time between redraw in microseconds.
	 */
	protected const int TIME_BETWEEN_REDRAW = 100000;

	/**
	 * Spinner frames.
	 */
	protected const array FRAMES = ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'];

	/**
	 * Returns the spinner frames.
	 */
	public function getFrames(): array
	{
		return static::FRAMES;
	}

	/**
	 * Returns the time between redraw in microseconds.
	 */
	public function getTimeBetweenRedraw(): int
	{
		return static::TIME_BETWEEN_REDRAW;
	}
}
