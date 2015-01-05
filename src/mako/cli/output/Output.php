<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cli\output;

use mako\cli\output\formatter\FormatterInterface;
use mako\cli\output\writer\WriterInterface;

/**
 * Output.
 *
 * @author  Frederic G. Østby
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
	 * Error output
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
	 * Formatter
	 * 
	 * @var \mako\cli\output\formatter\FormatterInterface|null
	 */

	protected $formatter;

	/**
	 * Is the output muted?
	 * 
	 * @var boolean
	 */

	 protected $muted = false;

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\cli\output\writer\WriterInterface        $standard   Standard writer
	 * @param   \mako\cli\output\writer\WriterInterface        $error      Error writer
	 * @param   \mako\cli\output\formatter\FormatterInterface  $formatter  Formatter
	 */

	public function __construct(WriterInterface $standard, WriterInterface $error, FormatterInterface $formatter = null)
	{
		$this->standard = $standard;

		$this->error = $error;

		$this->formatter = $formatter;
	}

	/**
	 * Returns the formatter.
	 * 
	 * @access  public
	 * @return  \mako\cli\output\formatter\FormatterInterface|null
	 */

	public function getFormatter()
	{
		return $this->formatter;
	}

	/**
	 * Mutes the output.
	 * 
	 * @access  public
	 */

	public function mute()
	{
		$this->muted = true;
	}

	/**
	 * Unmutes the output.
	 * 
	 * @access  public
	 */

	public function unmute()
	{
		$this->muted = false;
	}

	/**
	 * Writes string to output.
	 * 
	 * @access  public
	 * @param   string  $string  String to write
	 * @param   int     $type    Output type
	 */

	public function write($string, $type = Output::STANDARD)
	{
		if($this->muted)
		{
			return;
		}

		if($this->formatter !== null)
		{
			$string = $this->formatter->format($string);
		}

		$type === static::STANDARD ? $this->standard->write($string) : $this->error->write($string);
	}

	/**
	 * Writes string to output using the error writer.
	 * 
	 * @access  public
	 * @param   string  $string  String to write
	 * @param   int     $type    Output type
	 */

	public function error($string)
	{
		return $this->write($string, static::ERROR);
	} 

	/**
	 * Appends newline to string and writes it to output.
	 * 
	 * @access  public
	 * @param   string  $string  String to write
	 * @param   int     $type    Output type
	 */

	public function writeLn($string, $type = Output::STANDARD)
	{
		return $this->write($string . PHP_EOL, $type);
	}

	/**
	 * Appends newline to string and writes it to output using the error writer.
	 * 
	 * @access  public
	 * @param   string  $string  String to write
	 * @param   int     $type    Output type
	 */

	public function errorLn($string)
	{
		return $this->error($string . PHP_EOL);
	} 
}