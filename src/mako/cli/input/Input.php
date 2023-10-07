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
	 */
	public function __construct(
		protected ReaderInterface $reader,
		protected ArgvParser $arguments
	) {
	}

	/**
	 * Reads and returns user input.
	 */
	public function read(): string
	{
		return $this->reader->read();
	}

	/**
	 * Returns the argument parser.
	 */
	public function getArgumentParser(): ArgvParser
	{
		return $this->arguments;
	}

	/**
	 * Returns all the arguments passed to the script.
	 */
	public function getArguments(): array
	{
		return $this->arguments->parse();
	}

	/**
	 * Returns the argument associated with the given name.
	 */
	public function getArgument(int|string $name, mixed $default = null): mixed
	{
		return $this->arguments->getArgumentValue($name, $default);
	}
}
