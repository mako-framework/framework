<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output;

use mako\cli\Environment;
use mako\cli\output\formatter\FormatterInterface;
use mako\cli\output\writer\WriterInterface;

use function var_export;

/**
 * Output.
 */
class Output
{
	/**
	 * Standard output.
	 *
	 * @var int
	 */
	public const STANDARD = 1;

	/**
	 * Error output.
	 *
	 * @var int
	 */
	public const ERROR = 2;

	/**
	 * Is the output muted?
	 */
	protected bool $muted = false;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected WriterInterface $standard,
		protected WriterInterface $error,
		protected ?FormatterInterface $formatter = null,
		protected Environment $environment = new Environment
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
			if ($this->environment->hasAnsiSupport() === false || $writer->isDirect() === false) {
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
		if ($this->environment->hasAnsiSupport()) {
			$this->write("\e[H\e[2J");
		}
	}

	/**
	 * Clears the current line.
	 */
	public function clearLine(): void
	{
		if ($this->environment->hasAnsiSupport()) {
			$this->write("\r\33[2K");
		}
	}

	/**
	 * Clears n lines.
	 */
	public function clearLines(int $lines): void
	{
		if ($this->environment->hasAnsiSupport()) {
			for ($i = 0; $i < $lines; $i++) {
				if ($i > 0) {
					$this->write("\033[F");
				}

				$this->clearLine();
			}
		}
	}
}
