<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\components;

use mako\cli\output\components\frame\Theme;
use mako\cli\output\components\traits\StringHelperTrait;
use mako\cli\output\Output;

use function floor;
use function max;
use function str_repeat;

/**
 * Frame component.
 */
class Frame
{
	use StringHelperTrait;

	/**
	 * Alert width.
	 */
	protected int $width;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Output $output,
		protected Theme $theme = new Theme,
		?int $width = null
	) {
		$this->width = $width ?? $output->environment->getWidth();
	}

	/**
	 * Renders a frame.
	 */
	public function render(string $content, string $title = ''): string
	{
		$topCornersWidth = $this->getVisibleStringWidth($this->theme->getTopLeftCorner() . $this->theme->getTopRightCorner());
		$bottomCornersWidth = $this->getVisibleStringWidth($this->theme->getBottomLeftCorner() . $this->theme->getBottomRightCorner());
		$horizontalLineWidth = $this->getVisibleStringWidth($this->theme->getHorizontalLine());
		$sidesWidth = $this->getVisibleStringWidth($this->theme->getVerticalLine()) * 2;

		$frame = '';

		// Render top border

		$titleWidth = 0;

		if ($title !== '') {
			$title = "{$this->theme->getHorizontalLine()} {$title} ";
			$titleWidth = $this->getVisibleStringWidth($title);
		}

		$repeat = max(0, (int) floor(($this->width - $topCornersWidth - $titleWidth) / $horizontalLineWidth));

		$frame .= $this->theme->getTopLeftCorner();
		$frame .= $title;
		$frame .= str_repeat($this->theme->getHorizontalLine(), $repeat);
		$frame .= $this->theme->getTopRightCorner();
		$frame .= PHP_EOL;

		// Render content

		$maxContentWidth = max(0, $this->width - $sidesWidth - 2); // -2 for padding

		$lines = $this->wordWrap($content, $maxContentWidth, returnAsArray: true);

		foreach ($lines as $line) {
			$frame .= $this->theme->getVerticalLine();
			$frame .= ' ';
			$frame .= $line;
			$frame .= ' ';
			$frame .= str_repeat(' ', max(0, $maxContentWidth - $this->getVisibleStringWidth($line)));
			$frame .= $this->theme->getVerticalLine();
			$frame .= PHP_EOL;
		}

		// Render bottom border

		$repeat = max(0, (int) floor(($this->width - $bottomCornersWidth) / $horizontalLineWidth));

		$frame .= $this->theme->getBottomLeftCorner();
		$frame .= str_repeat($this->theme->getHorizontalLine(), $repeat);
		$frame .= $this->theme->getBottomRightCorner();
		$frame .= PHP_EOL;

		return $frame;
	}

	/**
	 * Draws a frame.
	 */
	public function draw(string $content, string $title = '', int $writer = Output::STANDARD): void
	{
		$this->output->write($this->render($content, $title), $writer);
	}
}
