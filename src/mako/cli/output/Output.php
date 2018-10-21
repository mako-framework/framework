<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output;

use mako\cli\output\formatter\FormatterInterface;
use mako\cli\output\writer\WriterInterface;

use function getenv;

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
	 * Do we have ANSI support?
	 *
	 * @var bool
	 */
	protected $hasAnsiSupport;

	/**
	 * Is the output muted?
	 *
	 * @var bool
	 */
	protected $muted = false;

	/**
	 * Constructor.
	 *
	 * @param \mako\cli\output\writer\WriterInterface            $standard       Standard writer
	 * @param \mako\cli\output\writer\WriterInterface            $error          Error writer
	 * @param \mako\cli\output\formatter\FormatterInterface|null $formatter      Formatter
	 * @param bool|null                                          $hasAnsiSupport Do we have ANSI support?
	 */
	public function __construct(WriterInterface $standard, WriterInterface $error, FormatterInterface $formatter = null, bool $hasAnsiSupport = null)
	{
		$this->standard = $standard;

		$this->error = $error;

		$this->formatter = $formatter;

		// Determine if we have ansi support

		if($hasAnsiSupport === null)
		{
			$hasAnsiSupport = DIRECTORY_SEPARATOR === '/' || (false !== getenv('ANSICON') || 'ON' === getenv('ConEmuANSI'));
		}

		$this->hasAnsiSupport = $hasAnsiSupport;
	}

	/**
	 * Do we have ANSI support?
	 *
	 * @return bool
	 */
	public function hasAnsiSupport(): bool
	{
		return $this->hasAnsiSupport;
	}

	/**
	 * Sets the formatter.
	 *
	 * @param \mako\cli\output\formatter\FormatterInterface $formatter Formatter
	 */
	public function setFormatter(FormatterInterface $formatter)
	{
		$this->formatter = $formatter;
	}

	/**
	 * Returns the formatter.
	 *
	 * @return \mako\cli\output\formatter\FormatterInterface|null
	 */
	public function getFormatter()
	{
		return $this->formatter;
	}

	/**
	 * Mutes the output.
	 */
	public function mute()
	{
		$this->muted = true;
	}

	/**
	 * Unmutes the output.
	 */
	public function unmute()
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
	public function write(string $string, int $writer = Output::STANDARD)
	{
		if($this->muted)
		{
			return;
		}

		$writer = ($writer === static::STANDARD) ? $this->standard : $this->error;

		if($this->formatter !== null)
		{
			if($this->hasAnsiSupport === false || $writer->isDirect() === false)
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
	public function error(string $string)
	{
		$this->write($string, static::ERROR);
	}

	/**
	 * Appends newline to string and writes it to output.
	 *
	 * @param string $string String to write
	 * @param int    $writer Output type
	 */
	public function writeLn(string $string, int $writer = Output::STANDARD)
	{
		$this->write($string . PHP_EOL, $writer);
	}

	/**
	 * Appends newline to string and writes it to output using the error writer.
	 *
	 * @param string $string String to write
	 */
	public function errorLn(string $string)
	{
		$this->writeLn($string, static::ERROR);
	}

	/**
	 * Clears the screen.
	 */
	public function clear()
	{
		if($this->hasAnsiSupport)
		{
			$this->write("\e[H\e[2J");
		}
	}

	/**
	 * Clears the current line.
	 */
	public function clearLine()
	{
		if($this->hasAnsiSupport)
		{
			$this->write("\r\33[2K");
		}
	}

	/**
	 * Clears n lines.
	 *
	 * @param int $lines Number of lines to clear
	 */
	public function clearLines(int $lines)
	{
		if($this->hasAnsiSupport)
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
