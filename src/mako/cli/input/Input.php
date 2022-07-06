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
 */
class Input
{
	/**
	 * Constructor.
	 *
	 * @param \mako\cli\input\reader\ReaderInterface $reader    Reader instance
	 * @param \mako\cli\input\arguments\ArgvParser   $arguments Argument parser
	 */
	public function __construct(
		protected ReaderInterface $reader,
		protected ArgvParser $arguments
	)
	{}

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
	public function getArgument(int|string $name, mixed $default = null): mixed
	{
		return $this->arguments->getArgumentValue($name, $default);
	}
}
