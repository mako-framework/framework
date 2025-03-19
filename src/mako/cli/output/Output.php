<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output;

use mako\cli\Environment;
use mako\cli\output\formatter\FormatterInterface;
use mako\cli\output\writer\Error;
use mako\cli\output\writer\Standard;
use mako\cli\output\writer\WriterInterface;

use function var_export;

/**
 * Output.
 */
class Output
{
	/**
	 * Standard output.
	 */
	public const int STANDARD = 1;

	/**
	 * Error output.
	 */
	public const int ERROR = 2;

	/**
	 * Is the output muted?
	 */
	protected bool $muted = false;

	/**
	 * Constructor.
	 */
	public function __construct(
		public protected(set) WriterInterface $standard = new Standard,
		public protected(set) WriterInterface $error = new Error,
		public protected(set) Environment $environment = new Environment,
		public protected(set) ?FormatterInterface $formatter = null,
		public protected(set) ?Cursor $cursor = null
	) {
	}

	/**
	 * Returns the chosen writer.
	 */
	public function getWriter(int $writer = Output::STANDARD): WriterInterface
	{
		return ($writer === static::STANDARD) ? $this->standard : $this->error;
	}

	/**
	 * Sets the formatter.
	 */
	public function setFormatter(FormatterInterface $formatter): void
	{
		$this->formatter = $formatter;
	}

	/**
	 * Returns the formatter.
	 */
	public function getFormatter(): ?FormatterInterface
	{
		return $this->formatter;
	}

	/**
	 * Returns the environment.
	 */
	public function getEnvironment(): Environment
	{
		return $this->environment;
	}

	/**
	 * Returns the cursor.
	 */
	public function getCursor(): ?Cursor
	{
		return $this->cursor;
	}

	/**
	 * Mutes the output.
	 */
	public function mute(): void
	{
		$this->muted = true;
	}

	/**
	 * Unmutes the output.
	 */
	public function unmute(): void
	{
		$this->muted = false;
	}

	/**
	 * Is the output muted?
	 */
	public function isMuted(): bool
	{
		return $this->muted;
	}

	/**
	 * Writes string to output.
	 */
	public function write(string $string, int $writer = Output::STANDARD): void
	{
		if ($this->muted) {
			return;
		}

		$writer = $this->getWriter($writer);

		if ($this->formatter !== null) {
			if ($this->environment->hasAnsiSupport() === false || $this->environment->noColor() || $writer->isDirect() === false) {
				$string = $this->formatter->stripTags($string);
			}

			$string = $this->formatter->format($string);
		}

		$writer->write($string);
	}

	/**
	 * Writes string to output using the error writer.
	 */
	public function error(string $string): void
	{
		$this->write($string, static::ERROR);
	}

	/**
	 * Appends newline to string and writes it to output.
	 */
	public function writeLn(string $string, int $writer = Output::STANDARD): void
	{
		$this->write($string . PHP_EOL, $writer);
	}

	/**
	 * Appends newline to string and writes it to output using the error writer.
	 */
	public function errorLn(string $string): void
	{
		$this->writeLn($string, static::ERROR);
	}

	/**
	 * Dumps a value to the output.
	 */
	public function dump(mixed $value, int $writer = Output::STANDARD): void
	{
		$this->getWriter($writer)->write(var_export($value, true) . PHP_EOL);
	}

	/**
	 * Clears the screen.
	 */
	public function clear(): void
	{
		if ($this->cursor && $this->environment->hasAnsiSupport()) {
			$this->cursor->clearScreen();
		}
	}

	/**
	 * Clears the current line.
	 */
	public function clearLine(): void
	{
		if ($this->cursor && $this->environment->hasAnsiSupport()) {
			$this->cursor->clearLine();
		}
	}

	/**
	 * Clears n lines.
	 */
	public function clearLines(int $lines): void
	{
		if ($this->cursor && $this->environment->hasAnsiSupport()) {
			$this->cursor->clearLines($lines);
		}
	}

	/**
	 * Hides the cursor.
	 */
	public function hideCursor(): void
	{
		if ($this->cursor && $this->environment->hasAnsiSupport()) {
			$this->cursor->hide();
		}
	}

	/**
	 * Shows the cursor.
	 */
	public function showCursor(): void
	{
		if ($this->cursor && $this->environment->hasAnsiSupport()) {
			$this->cursor->show();
		}
	}

	/**
	 * Restores the cursor.
	 */
	public function restoreCursor(): void
	{
		if ($this->cursor && $this->environment->hasAnsiSupport()) {
			$this->cursor->restore();
		}
	}
}
