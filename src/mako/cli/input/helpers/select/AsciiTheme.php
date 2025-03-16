<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\helpers\select;

/**
 * Ascii theme.
 */
class AsciiTheme extends Theme
{
	/**
	 * {@inheritDoc}
	 */
	protected const string ACTIVE_POINTER = '>';

	/**
	 * {@inheritDoc}
	 */
	protected const string INACTIVE_POINTER = ' ';

	/**
	 * {@inheritDoc}
	 */
	protected const string SELECTED = '[X]';

	/**
	 * {@inheritDoc}
	 */
	protected const string UNSELECTED = '[ ]';
}
