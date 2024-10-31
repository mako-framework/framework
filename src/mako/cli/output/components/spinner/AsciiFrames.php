<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\components\spinner;

/**
 * Ascii frames.
 */
class AsciiFrames extends Frames
{
	/**
	 * {@inheritDoc}
	 */
	protected const int TIME_BETWEEN_REDRAW = 200000;

	/**
	 * {@inheritDoc}
	 */
	protected const array FRAMES = [')', '|', '('];
}
