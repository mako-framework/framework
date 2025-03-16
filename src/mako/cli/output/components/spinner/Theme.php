<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\components\spinner;

use function array_map;
use function sprintf;

/**
 * Base theme.
 */
class Theme
{
	/**
	 * Time between redraw in microseconds.
	 */
	protected const int TIME_BETWEEN_REDRAW = 100_000;

	/**
	 * Spinner frames.
	 */
	protected const array FRAMES = ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'];

	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $template = '%s'
	) {
	}

	/**
	 * Returns the spinner frames.
	 */
	public function getFrames(): array
	{
		return array_map(fn ($frame) => sprintf($this->template, $frame), static::FRAMES);
	}

	/**
	 * Returns the time between redraw in microseconds.
	 */
	public function getTimeBetweenRedraw(): int
	{
		return static::TIME_BETWEEN_REDRAW;
	}
}
