<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\reactor;

/**
 * Command interface.
 */
interface CommandInterface
{
	/**
	 * Success status code.
	 *
	 * @var int
	 */
	public const STATUS_SUCCESS = 0;

	/**
	 * Error status code.
	 *
	 * @var int
	 */
	public const STATUS_ERROR = 1;

	/**
	 * Returns the command.
	 */
	public function getCommand(): ?string;

	/**
	 * Returns the command description.
	 */
	public function getDescription(): string;

	/**
	 * Returns the command arguments.
	 */
	public function getArguments(): array;
}
