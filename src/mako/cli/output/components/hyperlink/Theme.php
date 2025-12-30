<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\components\hyperlink;

use function sprintf;

/**
 * Base theme.
 */
class Theme
{
	/**
	 * Arrow.
	 */
	protected const string ARROW = '↗';

	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $template = '%s'
	) {
	}

	/**
	 * Returns the format.
	 */
	public function getFormat(): string
	{
		return sprintf($this->template, '%s [' . static::ARROW . ']');
	}
}
