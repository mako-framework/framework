<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cli\input;

use mako\cli\input\reader\ReaderInterface;

/**
 * Input.
 *
 * @author  Frederic G. Østby
 */

class Input
{
	/**
	 * Regex that matches named arguments.
	 * 
	 * @var string
	 */

	const NAMED_ARGUMENT_REGEX = '/--([a-z0-9-_]+)(=(.*))?/iu';

	/**
	 * Reader.
	 * 
	 * @var \mako\cli\input\reader\ReaderInterface
	 */

	protected $reader;

	/**
	 * Arguments.
	 * 
	 * @var array
	 */

	 protected $arguments;

	/**
	 * Constructor.
	 * 
	 * @var \mako\cli\input\reader\ReaderInterface  $reader     Reader instance
	 * @var null|array                              $arguments  Array of arguments passed to script
	 */

	public function __construct(ReaderInterface $reader, array $arguments = null)
	{
		$this->reader = $reader;

		$this->arguments = $this->parseArguments($arguments ?: $_SERVER['argv']);
	}

	/**
	 * Parses parameters.
	 * 
	 * @access  protected
	 * @param   array      $arguments  Arguments
	 * @return  array
	 */

	protected function parseArguments(array $arguments)
	{
		$parsed = [];

		$numericIndex = 1;

		foreach($arguments as $argument)
		{
			if(preg_match(static::NAMED_ARGUMENT_REGEX, $argument) === 1)
			{
				list($name, $value) = explode('=', substr($argument, 2), 2) + [null, null];

				$parsed[$name] = $value === null ? true : $value;
			}
			else
			{
				$parsed['arg' . $numericIndex++] = $argument;
			}
		}

		return $parsed;
	}

	/**
	 * Reads and returns user input.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function read()
	{
		return $this->reader->read();
	}

	/**
	 * Returns all the arguments passed to the script.
	 * 
	 * @access  public
	 * @return  array
	 */

	 public function getArguments()
	 {
	 	return $this->arguments;
	 }

	/**
	 * Returns the argument associated with the given name.
	 * 
	 * @access  public
	 * @param   int|string  $name     Parameter number or name
	 * @param   null|mixed  $default  Default value
	 * @return  mixed
	 */

	public function getArgument($name, $default = null)
	{
		if(is_int($name))
		{
			$name = 'arg' . $name;
		}

		return isset($this->arguments[$name]) ? $this->arguments[$name] : $default;
	}
}