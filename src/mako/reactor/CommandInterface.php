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
	 */
	public const int STATUS_SUCCESS = 0;

	/**
	 * Error status code.
	 */
	public const int STATUS_ERROR = 1;

	/**
	 * Returns the command.
	 *
	 * @deprecated
	 */
	public function getCommand(): ?string;

	/**
	 * Returns the command description.
	 *
	 * @deprecated
	 */
	public function getDescription(): string;

	/**
	 * Returns the command arguments.
	 *
	 * @deprecated
	 */
	public function getArguments(): array;
}
