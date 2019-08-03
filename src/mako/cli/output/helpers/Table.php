<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\helpers;

use mako\cli\output\helpers\traits\HelperTrait;
use mako\cli\output\Output;
use RuntimeException;

use function array_sum;
use function array_values;
use function count;
use function implode;
use function str_repeat;

/**
 * Table helper.
 *
 * @author Frederic G. Østby
 */
class Table
{
	use HelperTrait;

	/**
	 * Output instance.
	 *
	 * @var \mako\cli\output\Output
	 */
	protected $output;

	/**
	 * Formatter.
	 *
	 * @var \mako\cli\output\formatter\FormatterInterface|null
	 */
	protected $formatter;

	/**
	 * Constructor.
	 *
	 * @param \mako\cli\output\Output $output Output instance
	 */
	public function __construct(Output $output)
	{
		$this->output = $output;

		$this->formatter = $output->getFormatter();
	}

	/**
	 * Checks if the number of cells in each row matches the number of columns.
	 *
	 * @param  array $columnNames Array of column names
	 * @param  array $rows        Array of rows
	 * @return bool
	 */
	protected function isValidInput(array $columnNames, array $rows): bool
	{
		$columns = count($columnNames);

		if(!empty($rows))
		{
			foreach($rows as $row)
			{
				if(count($row) !== $columns)
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Returns an array containing the maximum width of each column.
	 *
	 * @param  array $columnNames Array of column names
	 * @param  array $rows        Array of rows
	 * @return array
	 */
	protected function getColumnWidths(array $columnNames, array $rows): array
	{
		$columnWidths = [];

		// First we'll get the width of the column names

		foreach(array_values($columnNames) as $key => $value)
		{
			$columnWidths[$key] = $this->stringWidthWithoutFormatting($value);
		}

		// Then we'll go through each row and check if the cells are wider than the column names

		foreach($rows as $row)
		{
			foreach(array_values($row) as $key => $value)
			{
				$width = $this->stringWidthWithoutFormatting($value);

				if($width > $columnWidths[$key])
				{
					$columnWidths[$key] = $width;
				}
			}
		}

		// Return array of column widths

		return $columnWidths;
	}

	/**
	 * Builds a row separator.
	 *
	 * @param  array  $columnWidths Array of column widths
	 * @param  string $separator    Separator character
	 * @return string
	 */
	protected function buildRowSeparator(array $columnWidths, string $separator = '-'): string
	{
		$columns = count($columnWidths);

		return str_repeat($separator, array_sum($columnWidths) + (($columns * 4) - ($columns - 1))) . PHP_EOL;
	}

	/**
	 * Builds a table row.
	 *
	 * @param  array  $colums       Array of column values
	 * @param  array  $columnWidths Array of column widths
	 * @return string
	 */
	protected function buildTableRow(array $colums, array $columnWidths): string
	{
		$cells = [];

		foreach(array_values($colums) as $key => $value)
		{
			$cells[] = $value . str_repeat(' ', $columnWidths[$key] - $this->stringWidthWithoutFormatting($value));
		}

		return '| ' . implode(' | ', $cells) . ' |' . PHP_EOL;
	}

	/**
	 * Renders a table.
	 *
	 * @param  array             $columnNames Array of column names
	 * @param  array             $rows        Array of rows
	 * @throws \RuntimeException
	 * @return string
	 */
	public function render(array $columnNames, array $rows): string
	{
		if(!$this->isValidInput($columnNames, $rows))
		{
			throw new RuntimeException('The number of cells in each row must match the number of columns.');
		}

		$columnWidths = $this->getColumnWidths($columnNames, $rows);

		// Build table header

		$table = $this->buildRowSeparator($columnWidths)
		. $this->buildTableRow($columnNames, $columnWidths)
		. $this->buildRowSeparator($columnWidths);

		// Add table rows

		foreach($rows as $row)
		{
			$table .= $this->buildTableRow($row, $columnWidths);
		}

		// Add bottom border

		$table .= $this->buildRowSeparator($columnWidths);

		// Return table

		return $table;
	}

	/**
	 * Draws a table.
	 *
	 * @param array $columnNames Array of column names
	 * @param array $rows        Array of rows
	 * @param int   $writer      Output writer
	 */
	public function draw(array $columnNames, array $rows, int $writer = Output::STANDARD): void
	{
		$this->output->write($this->render($columnNames, $rows), $writer);
	}
}
