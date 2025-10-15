<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\components;

use mako\cli\exceptions\CliException;
use mako\cli\output\components\table\Theme;
use mako\cli\output\components\traits\StringHelperTrait;
use mako\cli\output\Output;

use function array_values;
use function count;
use function implode;
use function str_repeat;

/**
 * Table component.
 */
class Table
{
	use StringHelperTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Output $output,
		protected Theme $theme = new Theme
	) {
	}

	/**
	 * Checks if the number of cells in each row matches the number of columns.
	 */
	protected function isValidInput(array $columnNames, array $rows): bool
	{
		$columns = count($columnNames);

		if (!empty($rows)) {
			foreach ($rows as $row) {
				if (count($row) !== $columns) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Returns an array containing the maximum width of each column.
	 */
	protected function getColumnWidths(array $columnNames, array $rows): array
	{
		$columnWidths = [];

		// First we'll get the width of the column names

		foreach (array_values($columnNames) as $key => $value) {
			$columnWidths[$key] = $this->getVisibleStringWidth($value);
		}

		// Then we'll go through each row and check if the cells are wider than the column names

		foreach ($rows as $row) {
			foreach (array_values($row) as $key => $value) {
				$width = $this->getVisibleStringWidth($value);

				if ($width > $columnWidths[$key]) {
					$columnWidths[$key] = $width;
				}
			}
		}

		// Return array of column widths

		return $columnWidths;
	}

	/**
	 * Builds a row separator.
	 */
	protected function buildRowSeparator(array $columnWidths, string $junction, string $leftCorner, string $rightCorner): string
	{
		$columns = count($columnWidths);

		$separator = $leftCorner;

		for ($i = 0; $i < $columns; $i++) {
			$separator .= str_repeat($this->theme->getHorizontalLine(), $columnWidths[$i] + 2) . ($i < $columns - 1 ? $junction : '');
		}

		return $separator . $rightCorner . PHP_EOL;
	}

	/**
	 * Builds a table row.
	 */
	protected function buildTableRow(array $colums, array $columnWidths): string
	{
		$cells = [];

		foreach (array_values($colums) as $key => $value) {
			$cells[] = $value . str_repeat(' ', $columnWidths[$key] - $this->getVisibleStringWidth($value));
		}

		return $this->theme->getVerticalLine()
		. ' ' . implode(' ' . $this->theme->getVerticalLine() . ' ', $cells)
		. ' ' . $this->theme->getVerticalLine() . PHP_EOL;
	}

	/**
	 * Renders a table.
	 */
	public function render(array $columnNames, array $rows): string
	{
		if (!$this->isValidInput($columnNames, $rows)) {
			throw new CliException('The number of cells in each row must match the number of columns.');
		}

		$columnWidths = $this->getColumnWidths($columnNames, $rows);

		// Build table header

		$table = $this->buildRowSeparator($columnWidths, $this->theme->getTJunctionDown(), $this->theme->getTopLeftCorner(), $this->theme->getTopRightCorner())
		. $this->buildTableRow($columnNames, $columnWidths)
		. $this->buildRowSeparator($columnWidths, $this->theme->getJunction(), $this->theme->getTJunctionLeft(), $this->theme->getTJunctionRight());

		// Add table rows

		foreach ($rows as $row) {
			$table .= $this->buildTableRow($row, $columnWidths);
		}

		// Add bottom border

		$table .= $this->buildRowSeparator($columnWidths, $this->theme->getTJunctionUp(), $this->theme->getBottomLeftCorner(), $this->theme->getBottomRightCorner());

		// Return table

		return $table;
	}

	/**
	 * Draws a table.
	 */
	public function draw(array $columnNames, array $rows, int $writer = Output::STANDARD): void
	{
		$this->output->write($this->render($columnNames, $rows), $writer);
	}
}
