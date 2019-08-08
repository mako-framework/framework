<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input;

use mako\cli\input\arguments\ArgvParser;
use mako\cli\input\reader\ReaderInterface;

/**
 * Input.
 *
 * @author Frederic G. Østby
 */
class Input
{
	/**
	 * Reader.
	 *
	 * @var \mako\cli\input\reader\ReaderInterface
	 */
	protected $reader;

	/**
	 * Arguments.
	 *
	 * @var \mako\cli\input\arguments\ArgvParser
	 */
	protected $arguments;

	/**
	 * Constructor.
	 *
	 * @param \mako\cli\input\reader\ReaderInterface    $reader    Reader instance
	 * @param \mako\cli\input\arguments\ArgvParser|null $arguments Argument parser
	 */
	public function __construct(ReaderInterface $reader, ?ArgvParser $arguments = null)
	{
		$this->reader = $reader;

		$this->arguments = $arguments ?? new ArgvParser($_SERVER['argv']);
	}

	/**
	 * Reads and returns user input.
	 *
	 * @return string
	 */
	public function read(): string
	{
		return $this->reader->read();
	}

	/**
	 * Returns the argument parser.
	 *
	 * @return \mako\cli\input\arguments\ArgvParser
	 */
	public function getArgumentParser(): ArgvParser
	{
		return $this->arguments;
	}

	/**
	 * Returns all the arguments passed to the script.
	 *
	 * @return array
	 */
	public function getArguments(): array
	{
		return $this->arguments->parse();
	}

	/**
	 * Returns the argument associated with the given name.
	 *
	 * @param  int|string $name    Parameter number or name
	 * @param  mixed      $default Default value
	 * @return mixed
	 */
	public function getArgument($name, $default = null)
	{
		return $this->arguments->getArgumentValue($name, $default);
	}
}
