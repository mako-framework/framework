<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cli\output\helpers;

use mako\cli\output\Output;

use RuntimeException;

/**
 * Table helper.
 *
 * @author  Frederic G. Østby
 */

class Table
{
	/**
	 * Output instance.
	 *
	 * @var \mako\cli\output\Output
	 */

	protected $output;

	/**
	 * Formatter instance.
	 *
	 * @var null|\mako\cli\output\formatter\FormatterInterface
	 */

	protected $formatter;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\cli\output\Output  $output  Output instance
	 */

	public function __construct(Output $output)
	{
		$this->output = $output;

		$this->formatter = $this->output->getFormatter();
	}

	/**
	 * Checks if the number of cells in each row matches the number of columns.
	 *
	 * @access  protected
	 * @param   array      $columnNames  Array of column names
	 * @param   array      $rows         Array of rows
	 * @return  boolean
	 */

	protected function isValidInput(array $columnNames, array $rows)
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
	 * Returns the width of the string without formatting.
	 *
	 * @access  protected
	 * @param   string     $string  String to strip
	 * @return  string
	 */

	protected function stringWidthWithoutFormatting($string)
	{
		return mb_strwidth($this->formatter !== null ? $this->formatter->strip($string) : $string);
	}

	/**
	 * Returns an array containing the maximum width of each column.
	 *
	 * @access  protected
	 * @param   array      $columnNames  Array of column names
	 * @param   array      $rows         Array of rows
	 * @return  array
	 */

	protected function getColumnWidths(array $columnNames, array $rows)
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
	 * @access  protected
	 * @param   array      $columnWidths  Array of column widths
	 * @param   string     $separator     Separator character
	 * @return  string
	 */

	protected function buildRowSeparator(array $columnWidths, $separator = '-')
	{
		$columns = count($columnWidths);

		return str_repeat($separator, array_sum($columnWidths) + (($columns * 4) - ($columns - 1))) . PHP_EOL;
	}

	/**
	 * Builds a table row.
	 *
	 * @access  protected
	 * @param   array      $colums        Array of column values
	 * @param   array      $columnWidths  Array of column widths
	 * @return  string
	 */

	protected function buildTableRow(array $colums, array $columnWidths)
	{
		$cells = [];

		foreach(array_values($colums) as $key => $value)
		{
			$cells[] = $value . str_repeat(' ', $columnWidths[$key] - $this->stringWidthWithoutFormatting($value));
		}

	 	return '| ' . implode(' | ', $cells) .  ' |' . PHP_EOL;
	}

	/**
	 * Renders a table.
	 *
	 * @access  public
	 * @param   array   $columnNames  Array of column names
	 * @param   array   $rows         Array of rows
	 * @return  string
	 */

	public function render(array $columnNames, array $rows)
	{
		if(!$this->isValidInput($columnNames, $rows))
		{
			throw new RuntimeException(vsprintf("%s(): The number of cells in each row must match the number of columns.", [__METHOD__]));
		}

		$columnWidths = $this->getColumnWidths($columnNames, $rows);

		// Build table header

		$table = $this->buildRowSeparator($columnWidths);

		$table .= $this->buildTableRow($columnNames, $columnWidths);

		$table .= $this->buildRowSeparator($columnWidths);

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
	 * @access  public
	 * @param   array   $columnNames  Array of column names
	 * @param   array   $rows         Array of rows
	 * @param   int     $writer       Output writer
	 */

	public function draw(array $columnNames, array $rows, $writer = Output::STANDARD)
	{
		$this->output->write($this->render($columnNames, $rows), $writer);
	}
}