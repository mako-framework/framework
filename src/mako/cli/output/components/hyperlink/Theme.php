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
	 * Format.
	 */
	protected const string FORMAT = '%s [↗]';

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
		return sprintf($this->template, static::FORMAT);
	}
}
