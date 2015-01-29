<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cli\input\helpers;

use RuntimeException;

use mako\cli\input\helpers\Question;

/**
 * Secret helper.
 *
 * @author  Frederic G. Østby
 */

use mako\cli\input\Input;
use mako\cli\output\Output;

class Secret extends Question
{
	/**
	 * Input instance.
	 *
	 * @var \mako\cli\input\Input
	 */

	protected $input;

	/**
	 * Output instance.
	 *
	 * @var \mako\cli\output\Output
	 */

	protected $ouput;

	/**
	 * Do we have stty support?
	 *
	 * @var boolean
	 */

	protected static $hasStty;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\cli\input\Input    $input   Input instance
	 * @param   \mako\cli\output\Output  $output  Output instance
	 */

	public function __construct(Input $input, Output $output)
	{
		$this->input = $input;

		$this->output = $output;
	}

	/**
	 * Do we have stty support?
	 *
	 * @access  protected
	 * @return  boolean
	 */

	protected function hasStty()
	{
		if(static::$hasStty === null)
		{
			exec('stty 2>&1', $output, $status);

			static::$hasStty = $status === 0;
		}

		return static::$hasStty;
	}

	/**
	 * Writes question to output and returns hidden user input.
	 *
	 * @access  public
	 * @param   string      $question  Question to ask
	 * @param   null|mixed  $default   Default if no input is entered
	 * @param   boolean     $fallback  Fall back to non-hidden input?
	 * @return  string
	 */

	public function ask($question, $default = null, $fallback = false)
	{
		if(DIRECTORY_SEPARATOR === '\\' || $this->hasStty())
		{
			$this->output->write(trim($question) . ' ');

			if(DIRECTORY_SEPARATOR === '\\')
			{
				$answer = trim(shell_exec(__DIR__ . '/resources/hiddeninput.exe'));
			}
			else
			{
				$settings = shell_exec('stty -g');

				exec('stty -echo');

				$answer = $this->input->read();

				exec('stty ' . $settings);
			}

			$this->output->write(PHP_EOL);

			return empty($answer) ? $default : $answer;
		}
		elseif($fallback)
		{
			return parent::ask($question, $default);
		}
		else
		{
			throw new RuntimeException(vsprintf("%s(): Unable to hide the user input.", [__METHOD__]));
		}
	}
}