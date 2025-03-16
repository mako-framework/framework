<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output;

use Deprecated;
use mako\cli\input\reader\ReaderInterface;
use mako\cli\output\writer\WriterInterface;
use mako\cli\traits\SttyTrait;

use function sscanf;

/**
 * Cursor.
 */
class Cursor
{
	use SttyTrait;

	/**
	 * Is the cursor hidden?
	 */
	protected bool $hidden = false;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected WriterInterface $writer,
		protected ReaderInterface $reader,
	) {
	}

	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		$this->restore();
	}

	/**
	 * Is the cursor hidden?
	 */
	public function isHidden(): bool
	{
		return $this->hidden;
	}

	/**
	 * Hides the cursor.
	 */
	public function hide(): void
	{
		$this->writer->write("\033[?25l");

		$this->hidden = true;
	}

	/**
	 * Shows the cursor.
	 */
	public function show(): void
	{
		$this->writer->write("\033[?25h");

		$this->hidden = false;
	}

	/**
	 * Restores the cursor.
	 */
	public function restore(): void
	{
		if ($this->hidden) {
			$this->show();
		}
	}

	/**
	 * Moves the cursor to the beginning of the line.
	 */
	#[Deprecated('use the "moveToBeginningOfLine" method instead', 'Mako 11.2.0')]
	public function beginningOfLine(): void
	{
		$this->writer->write("\r");
	}

	/**
	 * Moves the cursor up.
	 */
	public function up(int $lines = 1): void
	{
		$this->writer->write("\033[{$lines}A");
	}

	/**
	 * Moves the cursor down.
	 */
	public function down(int $lines = 1): void
	{
		$this->writer->write("\033[{$lines}B");
	}

	/**
	 * Moves the cursor left.
	 */
	public function left(int $columns = 1): void
	{
		$this->writer->write("\033[{$columns}D");
	}

	/**
	 * Moves the cursor right.
	 */
	public function right(int $columns = 1): void
	{
		$this->writer->write("\033[{$columns}C");
	}

	/**
	 * Moves the cursor to a specific position.
	 */
	public function moveTo(int $row, int $column): void
	{
		$this->writer->write("\033[{$row};{$column}H");
	}

	/**
	 * Moves the cursor to the beginning of the line.
	 */
	public function moveToBeginningOfLine(): void
	{
		$this->writer->write("\r");
	}

	/**
	 * Moves the cursor to the end of the line.
	 */
	public function moveToEndOfLine(): void
	{
		$this->right(9999);
	}

	/*
	 * Returns the cursor position.
	 *
	 * @return array{row: int, column: int}
	 */
	public function getPosition(): array
	{
		$response = $this->sttySandbox(function (): string {
			$this->setSttySettings('-echo -icanon');

			$this->writer->write("\033[6n");

			$response = '';

			while (true) {
				$char = $this->reader->readCharacter();

				if ($char === 'R') {
					return "{$response}R";
				}

				$response .= $char;
			}
		});

		sscanf($response, "\033[%d;%dR", $row, $column);

		return ['row' => $row, 'column' => $column];
	}

	/**
	 * Clears the line.
	 */
	public function clearLine(): void
	{
		$this->writer->write("\r\33[2K");
	}

	/**
	 * Clears the line from the cursor.
	 */
	public function clearLineFromCursor(): void
	{
		$this->writer->write("\33[K");
	}

	/**
	 * Clears n lines.
	 */
	public function clearLines(int $lines): void
	{
		for ($i = 0; $i < $lines; $i++) {
			if ($i > 0) {
				$this->up();
			}

			$this->clearLine();
		}
	}

	/**
	 * Clears the screen.
	 */
	public function clearScreen(): void
	{
		$this->writer->write("\033[H\033[2J");
	}

	/**
	 * Clears the screen from the cursor.
	 */
	public function clearScreenFromCursor(): void
	{
		$this->writer->write("\033[J");
	}
}
