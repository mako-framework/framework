<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\helpers\confirmation;

/**
 * Ascii theme.
 */
class AsciiTheme extends Theme
{
	/**
	 * {@inheritDoc}
	 */
	protected const string SELECTED = '[X]';

	/**
	 * {@inheritDoc}
	 */
	protected const string UNSELECTED = '[ ]';
}
