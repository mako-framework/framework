<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\components;

use mako\cli\output\components\traits\StringHelperTrait;
use mako\cli\output\Output;

use function implode;
use function min;
use function sprintf;
use function str_repeat;

/**
 * Labels and values component.
 */
class LabelsAndValues
{
	use StringHelperTrait;

	/**
	 * Separator.
	 */
	protected const string SEPARATOR = '.';

	/**
	 * Margin width.
	 */
	protected const int MARGIN_WIDTH = 2;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Output $output,
		protected float|int $widthPercent = 100.0,
		protected int $maxWidth = PHP_INT_MAX,
		protected int $minSeparatorCount = 1,
		protected string $separatorTemplate = '%s'
	) {
	}

	/**
	 * Returns the width of the longest label and value combination.
	 */
	protected function getLongestLabelValueWidth(array $labelsAndValues): int
	{
		$longest = 0;

		foreach ($labelsAndValues as $label => $value) {
			if (($width = $this->getVisibleStringWidth("{$label}{$value}")) > $longest) {
				$longest = $width;
			}
		}

		return $longest;
	}

	/**
	 * Should we use the minimum number of separators?
	 */
	protected function useMinSeparators(int $width, int $longestLabeValuelWidth, int $minSeparators): bool
	{
		$separatorWidth = $width - $longestLabeValuelWidth - static::MARGIN_WIDTH;

		if ($separatorWidth < 0 || $separatorWidth < $minSeparators) {
			return true;
		}

		return false;
	}

	/**
	 * Renders the labels and values.
	 */
	public function render(array $labelsAndValues): string
	{
		$width = min($this->maxWidth, (int) ($this->output->environment->getWidth() * (min($this->widthPercent, 100.0) / 100.0)));

		$longestLabeValuelWidth = $this->getLongestLabelValueWidth($labelsAndValues);

		if ($this->useMinSeparators($width, $longestLabeValuelWidth, $this->minSeparatorCount)) {
			$width = $longestLabeValuelWidth + static::MARGIN_WIDTH + $this->minSeparatorCount;
		}

		$output = [];

		foreach ($labelsAndValues as $label => $value) {
			$times = $width - $this->getVisibleStringWidth("{$label}{$value}") - static::MARGIN_WIDTH;

			$output[] = "{$label} " . sprintf($this->separatorTemplate, str_repeat(static::SEPARATOR, $times)) . " {$value}";
		}

		return implode(PHP_EOL, $output) . PHP_EOL;
	}

	/**
	 * Draws the labels and values.
	 */
	public function draw(array $labelsAndValues, int $writer = Output::STANDARD): void
	{
		$this->output->write($this->render($labelsAndValues), $writer);
	}
}
