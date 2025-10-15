<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\components\frame;

/**
 * Ascii theme.
 */
class AsciiTheme extends Theme
{
	/**
	 * {@inheritDoc}
	 */
	protected const string HORIZONTAL_LINE = '-';

	/**
	 * {@inheritDoc}
	 */
	protected const string VERTICAL_LINE = '|';

	/**
	 * {@inheritDoc}
	 */
	protected const string CORNER_TOP_LEFT = '+';

	/**
	 * {@inheritDoc}
	 */
	protected const string CORNER_TOP_RIGHT = '+';

	/**
	 * {@inheritDoc}
	 */
	protected const string CORNER_BOTTOM_LEFT = '+';

	/**
	 * {@inheritDoc}
	 */
	protected const string CORNER_BOTTOM_RIGHT = '+';
}
