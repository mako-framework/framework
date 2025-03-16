<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\components\progress;

use function sprintf;

/**
 * Base theme.
 */
class Theme
{
	/**
	 * Empty progress bar character.
	 */
	protected const string EMPTY = '─';

	/**
	 * Filled progress bar character.
	 */
	protected const string FILLED = '█';

	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $emptyTemplate = '%s',
		protected string $filledTemplate = '%s'
	) {
	}

	/**
	 * Returns the empty progress bar character.
	 */
	public function getEmpty(): string
	{
		return sprintf($this->emptyTemplate, static::EMPTY);
	}

	/**
	 * Returns the filled progress bar character.
	 */
	public function getFilled(): string
	{
		return sprintf($this->filledTemplate, static::FILLED);
	}
}
