<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\helpers;

use mako\cli\exceptions\CliException;
use mako\cli\output\formatter\FormatterInterface;
use mako\cli\output\helpers\traits\HelperTrait;
use mako\cli\output\Output;

use function array_values;
use function count;
use function implode;
use function str_repeat;

/**
 * Table helper.
 */
class Table
{
	use HelperTrait;

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
	 * Formatter.
	 */
	protected null|FormatterInterface $formatter = null;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Output $output
	) {
		$this->formatter = $output->getFormatter();
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
			$columnWidths[$key] = $this->stringWidthWithoutFormatting($value);
		}

		// Then we'll go through each row and check if the cells are wider than the column names

		foreach ($rows as $row) {
			foreach (array_values($row) as $key => $value) {
				$width = $this->stringWidthWithoutFormatting($value);

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
			$separator .= str_repeat(static::HORIZONTAL_LINE, $columnWidths[$i] + 2) . ($i < $columns - 1 ? $junction : '');
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
			$cells[] = $value . str_repeat(' ', $columnWidths[$key] - $this->stringWidthWithoutFormatting($value));
		}

		return static::VERTICAL_LINE . ' ' . implode(' ' . static::VERTICAL_LINE . ' ', $cells) . ' ' . static::VERTICAL_LINE . PHP_EOL;
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

		$table = $this->buildRowSeparator($columnWidths, static::T_JUNCTION_DOWN, static::CORNER_TOP_LEFT, static::CORNER_TOP_RIGHT)
		. $this->buildTableRow($columnNames, $columnWidths)
		. $this->buildRowSeparator($columnWidths, static::JUNCTION, static::T_JUNCTION_LEFT, static::T_JUNCTION_RIGHT);

		// Add table rows

		foreach ($rows as $row) {
			$table .= $this->buildTableRow($row, $columnWidths);
		}

		// Add bottom border

		$table .= $this->buildRowSeparator($columnWidths, static::T_JUNCTION_UP, static::CORNER_BOTTOM_LEFT, static::CORNER_BOTTOM_RIGHT);

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
