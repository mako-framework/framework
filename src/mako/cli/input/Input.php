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
		public protected(set) ArgvParser $argumentParser
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
	 * Reads and returns a single character.
	 */
	public function readCharacter(): string
	{
		return $this->reader->readCharacter();
	}

	/**
	 * Returns the argument parser.
	 */
	public function getArgumentParser(): ArgvParser
	{
		return $this->argumentParser;
	}

	/**
	 * Returns all the arguments passed to the script.
	 */
	public function getArguments(): array
	{
		return $this->argumentParser->parse();
	}

	/**
	 * Returns the argument associated with the given name.
	 */
	public function getArgument(int|string $name, mixed $default = null): mixed
	{
		return $this->argumentParser->getArgumentValue($name, $default);
	}
}
