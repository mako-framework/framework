<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\components\spinner;

/**
 * Ascii theme.
 */
class AsciiTheme extends Theme
{
	/**
	 * {@inheritDoc}
	 */
	protected const int TIME_BETWEEN_REDRAW = 200_000;

	/**
	 * {@inheritDoc}
	 */
	protected const array FRAMES = [')', '|', '('];
}
