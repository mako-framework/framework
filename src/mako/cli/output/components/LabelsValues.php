<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\components;

use mako\cli\output\components\traits\HelperTrait;
use mako\cli\output\Output;

use function array_keys;
use function implode;
use function min;
use function sprintf;
use function str_repeat;

/**
 * Labels and values component.
 */
class LabelsValues
{
	use HelperTrait;

	/**
	 * Separator.
	 */
	protected const string SEPARATOR = '.';

	/**
	 * Margin width.
	 */
	protected const int MARGIN_WIDTH = 2;

	/**
	 * Percentage mode.
	 */
	protected const int MODE_PCT = 1;

	/**
	 * Minimum width mode.
	 */
	protected const int MODE_MIN = 2;

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
	 * Returns the width of the longest label.
	 */
	protected function getLongestLabelWidth(array $labels): int
	{
		$longestLabel = 0;

		foreach ($labels as $label) {
			if ($this->getVisibleStringWidth($label) > $longestLabel) {
				$longestLabel = $this->getVisibleStringWidth($label);
			}
		}

		return $longestLabel;
	}

	/**
	 * Returns the mode.
	 */
	protected function getMode(array $labelsAndValues, int $width, int $minSeparators): int
	{
		$longest = 0;

		foreach ($labelsAndValues as $label => $value) {
			$labelValueWidth = $this->getVisibleStringWidth("{$label}{$value}");

			if ($labelValueWidth > $longest) {
				$longest = $labelValueWidth;
			}
		}

		$separatorWidth = $width - $longest - static::MARGIN_WIDTH;

		if ($separatorWidth < 0 || $separatorWidth < $minSeparators) {
			return static::MODE_MIN;
		}

		return static::MODE_PCT;
	}

	/**
	 * Renders the labels and values.
	 */
	public function render(array $labelsAndValues): string
	{
		$width = min($this->maxWidth, (int) $this->output->environment->getWidth() * (min($this->widthPercent, 100.0) / 100.0));

		$mode = $this->getMode($labelsAndValues, $width, $this->minSeparatorCount);

		$longestLabelWidth = $this->getLongestLabelWidth(array_keys($labelsAndValues));

		$output = [];

		foreach ($labelsAndValues as $label => $value) {
			$labelWidth = $this->getVisibleStringWidth($label);
			$valueWidth = $this->getVisibleStringWidth($value);

			$times = match ($mode) {
				static::MODE_PCT => (int) ($width - $labelWidth - $valueWidth - static::MARGIN_WIDTH),
				static::MODE_MIN => (int) $this->minSeparatorCount + ($longestLabelWidth - $labelWidth),
			};

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
