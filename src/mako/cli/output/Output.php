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
 *
 * @author Frederic G. Østby
 */
class Output
{
	/**
	 * Standard output.
	 *
	 * @var int
	 */
	const STANDARD = 1;

	/**
	 * Error output.
	 *
	 * @var int
	 */
	const ERROR = 2;

	/**
	 * Standard writer.
	 *
	 * @var \mako\cli\output\writer\WriterInterface
	 */
	protected $standard;

	/**
	 * Error writer.
	 *
	 * @var \mako\cli\output\writer\WriterInterface
	 */
	protected $error;

	/**
	 * Formatter.
	 *
	 * @var \mako\cli\output\formatter\FormatterInterface|null
	 */
	protected $formatter;

	/**
	 * Environment.
	 *
	 * @var \mako\cli\Environment
	 */
	protected $environment;

	/**
	 * Is the output muted?
	 *
	 * @var bool
	 */
	protected $muted = false;

	/**
	 * Constructor.
	 *
	 * @param \mako\cli\output\writer\WriterInterface            $standard    Standard writer
	 * @param \mako\cli\output\writer\WriterInterface            $error       Error writer
	 * @param \mako\cli\output\formatter\FormatterInterface|null $formatter   Formatter
	 * @param \mako\cli\Environment|null                         $environment Environment
	 */
	public function __construct(WriterInterface $standard, WriterInterface $error, ?FormatterInterface $formatter = null, ?Environment $environment = null)
	{
		$this->standard = $standard;

		$this->error = $error;

		$this->formatter = $formatter;

		$this->environment = $environment ?? new Environment;
	}

	/**
	 * Returns the chosen writer.
	 *
	 * @param  int                                     $writer Writer
	 * @return \mako\cli\output\writer\WriterInterface
	 */
	public function getWriter(int $writer = Output::STANDARD): WriterInterface
	{
		return ($writer === static::STANDARD) ? $this->standard : $this->error;
	}

	/**
	 * Sets the formatter.
	 *
	 * @param \mako\cli\output\formatter\FormatterInterface $formatter Formatter
	 */
	public function setFormatter(FormatterInterface $formatter): void
	{
		$this->formatter = $formatter;
	}

	/**
	 * Returns the formatter.
	 *
	 * @return \mako\cli\output\formatter\FormatterInterface|null
	 */
	public function getFormatter(): ?FormatterInterface
	{
		return $this->formatter;
	}

	/**
	 * Returns the environment.
	 *
	 * @return \mako\cli\Environment
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
	 *
	 * @return bool
	 */
	public function isMuted(): bool
	{
		return $this->muted;
	}

	/**
	 * Writes string to output.
	 *
	 * @param string $string String to write
	 * @param int    $writer Output type
	 */
	public function write(string $string, int $writer = Output::STANDARD): void
	{
		if($this->muted)
		{
			return;
		}

		$writer = $this->getWriter($writer);

		if($this->formatter !== null)
		{
			if($this->environment->hasAnsiSupport() === false || $writer->isDirect() === false)
			{
				$string = $this->formatter->stripTags($string);
			}

			$string = $this->formatter->format($string);
		}

		$writer->write($string);
	}

	/**
	 * Writes string to output using the error writer.
	 *
	 * @param string $string String to write
	 */
	public function error(string $string): void
	{
		$this->write($string, static::ERROR);
	}

	/**
	 * Appends newline to string and writes it to output.
	 *
	 * @param string $string String to write
	 * @param int    $writer Output type
	 */
	public function writeLn(string $string, int $writer = Output::STANDARD): void
	{
		$this->write($string . PHP_EOL, $writer);
	}

	/**
	 * Appends newline to string and writes it to output using the error writer.
	 *
	 * @param string $string String to write
	 */
	public function errorLn(string $string): void
	{
		$this->writeLn($string, static::ERROR);
	}

	/**
	 * Dumps a value to the output.
	 *
	 * @param  mixed $value  Value
	 * @param  int   $writer Output type
	 * @return void
	 */
	public function dump($value, int $writer = Output::STANDARD): void
	{
		$this->getWriter($writer)->write(var_export($value, true) . PHP_EOL);
	}

	/**
	 * Clears the screen.
	 */
	public function clear(): void
	{
		if($this->environment->hasAnsiSupport())
		{
			$this->write("\e[H\e[2J");
		}
	}

	/**
	 * Clears the current line.
	 */
	public function clearLine(): void
	{
		if($this->environment->hasAnsiSupport())
		{
			$this->write("\r\33[2K");
		}
	}

	/**
	 * Clears n lines.
	 *
	 * @param int $lines Number of lines to clear
	 */
	public function clearLines(int $lines): void
	{
		if($this->environment->hasAnsiSupport())
		{
			for($i = 0; $i < $lines; $i++)
			{
				if($i > 0)
				{
					$this->write("\033[F");
				}

				$this->clearLine();
			}
		}
	}
}
