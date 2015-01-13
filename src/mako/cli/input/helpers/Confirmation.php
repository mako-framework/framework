<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cli\input\helpers;

/**
 * Confirmation helper.
 *
 * @author  Frederic G. Østby
 */

use mako\cli\input\Input;
use mako\cli\output\Output;

class Confirmation
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
	 * Returns an array where all array keys lower case.
	 * 
	 * @access  protected
	 * @param   array      $array  Array
	 * @return  array
	 */

	protected function normalizeKeys(array $array)
	{
		$normalized = [];

		foreach($array as $key => $value)
		{
			$normalized[mb_strtolower($key)] = $value;
		}

		return $normalized;
	}

	/**
	 * Returns a slash-separated list of valid options where the default one is highlighted as upper-case.
	 * 
	 * @access  public
	 * @param   array   $options  Answer options
	 * @param   string  $default  Default answer
	 * @return  string
	 */

	protected function getOptions(array $options, $default)
	{
		$highlighted = [];

		foreach(array_keys($options) as $option)
		{
			$highlighted[] = $option === $default ? mb_strtoupper($option) : $option;
		}

		return implode('/', $highlighted);
	}

	/**
	 * Asks user for confirmation and returns value corresponding to the chosen value.
	 * 
	 * @access  public
	 * @param   string   $question  Question to ask
	 * @param   string   $default   Default answer
	 * @param   array    $options   Answer options
	 * @return  boolean
	 */

	public function ask($question, $default = 'n', array $options = null)
	{
		$options = $options === null ? ['y' => true, 'n' => false] : $this->normalizeKeys($options);

		// Write question along with options to output

		$this->output->write(trim($question) . ' [' . $this->getOptions($options, $default) . '] ');

		// Read user input and make it lower-case

		$input = $this->input->read();

		$input = mb_strtolower(empty($input) ? $default : $input);

		// Check user input
		
		if(!isset($options[$input]))
		{
			// We got an invalid answer from the user so we'll just ask the same question again

			return $this->ask($question, $default, $options);
		}

		// Return boolean value of the user's answer
		
		return $options[$input];
	}
}