<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\components\frame;

use function sprintf;

/**
 * Base theme.
 */
class Theme
{
	/**
	 * Horizontal line.
	 */
	protected const string HORIZONTAL_LINE = '━';

	/**
	 * Vertical line.
	 */
	protected const string VERTICAL_LINE = '┃';

	/**
	 * Top left corner.
	 */
	protected const string CORNER_TOP_LEFT = '┏';

	/**
	 * Top right corner.
	 */
	protected const string CORNER_TOP_RIGHT = '┓';

	/**
	 * Bottom left corner.
	 */
	protected const string CORNER_BOTTOM_LEFT = '┗';

	/**
	 * Bottom right corner.
	 */
	protected const string CORNER_BOTTOM_RIGHT = '┛';

	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $template = '%s',
	) {
	}

	/**
	 * Returns the horizontal line.
	 */
	public function getHorizontalLine(): string
	{
		return sprintf($this->template, static::HORIZONTAL_LINE);
	}

	/**
	 * Returns the vertical line.
	 */
	public function getVerticalLine(): string
	{
		return sprintf($this->template, static::VERTICAL_LINE);
	}

	/**
	 * Returns the top left corner.
	 */
	public function getTopLeftCorner(): string
	{
		return sprintf($this->template, static::CORNER_TOP_LEFT);
	}

	/**
	 * Returns the top right corner.
	 */
	public function getTopRightCorner(): string
	{
		return sprintf($this->template, static::CORNER_TOP_RIGHT);
	}

	/**
	 * Returns the bottom left corner.
	 */
	public function getBottomLeftCorner(): string
	{
		return sprintf($this->template, static::CORNER_BOTTOM_LEFT);
	}

	/**
	 * Returns the bottom right corner.
	 */
	public function getBottomRightCorner(): string
	{
		return sprintf($this->template, static::CORNER_BOTTOM_RIGHT);
	}
}
