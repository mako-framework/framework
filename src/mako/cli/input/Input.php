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
	 * Is the input interactive?
	 */
	protected $isInteractive = true;

	/**
	 * Constructor.
	 */
	public function __construct(
		public protected(set) ReaderInterface $reader,
		public protected(set) ArgvParser $argumentParser
	) {
	}

	/**
	 * Makes the input interactive.
	 */
	public function makeInteractive(): void
	{
		$this->isInteractive = true;
	}

	/**
	 * Makes the input non-interactive.
	 */
	public function makeNonInteractive(): void
	{
		$this->isInteractive = false;
	}

	/**
	 * Is the input interactive?
	 */
	public function isInteractive(): bool
	{
		return $this->isInteractive;
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
	 * Reads and returns a specified number of bytes.
	 */
	public function readBytes(int $length): string
	{
		return $this->reader->readBytes($length);
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
	public function getArguments(bool $forceParse = false): array
	{
		return $this->argumentParser->parse(forceParse: $forceParse);
	}

	/**
	 * Returns the argument associated with the given name.
	 */
	public function getArgument(int|string $name, mixed $default = null): mixed
	{
		return $this->argumentParser->getArgumentValue($name, $default);
	}
}
