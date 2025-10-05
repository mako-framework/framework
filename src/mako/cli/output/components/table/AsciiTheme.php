<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\components\table;

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
	protected const string T_JUNCTION_DOWN = '+';

	/**
	 * {@inheritDoc}
	 */
	protected const string T_JUNCTION_UP = '+';

	/**
	 * {@inheritDoc}
	 */
	protected const string T_JUNCTION_LEFT = '+';

	/**
	 * {@inheritDoc}
	 */
	protected const string T_JUNCTION_RIGHT = '+';

	/**
	 * {@inheritDoc}
	 */
	protected const string JUNCTION = '+';

	/**
	 * {@inheritDoc}
	 */
	protected const string CORNER_BOTTOM_LEFT = '+';

	/**
	 * {@inheritDoc}
	 */
	protected const string CORNER_BOTTOM_RIGHT = '+';
}
