<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\components\table;

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
	 * Down t-junction.
	 */
	protected const string T_JUNCTION_DOWN = '┳';

	/**
	 * Up t-junction.
	 */
	protected const string T_JUNCTION_UP = '┻';

	/**
	 * Left t-junction.
	 */
	protected const string T_JUNCTION_LEFT = '┣';

	/**
	 * Right t-junction.
	 */
	protected const string T_JUNCTION_RIGHT = '┫';

	/**
	 * Junction.
	 */
	protected const string JUNCTION = '╋';

	/**
	 * Bottom left corner.
	 */
	protected const string CORNER_BOTTOM_LEFT = '┗';

	/**
	 * Bottom right corner.
	 */
	protected const string CORNER_BOTTOM_RIGHT = '┛';

	/**
	 * Returns the horizontal line.
	 */
	public function getHorizontalLine(): string
	{
		return static::HORIZONTAL_LINE;
	}

	/**
	 * Returns the vertical line.
	 */
	public function getVerticalLine(): string
	{
		return static::VERTICAL_LINE;
	}

	/**
	 * Returns the top left corner.
	 */
	public function getTopLeftCorner(): string
	{
		return static::CORNER_TOP_LEFT;
	}

	/**
	 * Returns the top right corner.
	 */
	public function getTopRightCorner(): string
	{
		return static::CORNER_TOP_RIGHT;
	}

	/**
	 * Returns the down t-junction.
	 */
	public function getTJunctionDown(): string
	{
		return static::T_JUNCTION_DOWN;
	}

	/**
	 * Returns the up t-junction.
	 */
	public function getTJunctionUp(): string
	{
		return static::T_JUNCTION_UP;
	}

	/**
	 * Returns the left t-junction.
	 */
	public function getTJunctionLeft(): string
	{
		return static::T_JUNCTION_LEFT;
	}

	/**
	 * Returns the right t-junction.
	 */
	public function getTJunctionRight(): string
	{
		return static::T_JUNCTION_RIGHT;
	}

	/**
	 * Returns the junction.
	 */
	public function getJunction(): string
	{
		return static::JUNCTION;
	}

	/**
	 * Returns the bottom left corner.
	 */
	public function getBottomLeftCorner(): string
	{
		return static::CORNER_BOTTOM_LEFT;
	}

	/**
	 * Returns the bottom right corner.
	 */
	public function getBottomRightCorner(): string
	{
		return static::CORNER_BOTTOM_RIGHT;
	}
}
