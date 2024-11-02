<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\components\progress;

/**
 * Ascii progress bar.
 */
class AsciiProgressBar extends ProgressBar
{
	/**
	 * Empty progress bar.
	 */
	protected const string EMPTY = '-';

	/**
	 * Filled progress bar.
	 */
	protected const string FILLED = '=';
}
