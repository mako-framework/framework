<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\reactor;

/**
 * Command interface.
 *
 * @author Frederic G. Østby
 */
interface CommandInterface
{
	/**
	 * Success status code.
	 *
	 * @var int
	 */
	const STATUS_SUCCESS = 0;

	/**
	 * Error status code.
	 *
	 * @var int
	 */
	const STATUS_ERROR = 1;

	/**
	 * Returns the command description.
	 *
	 * @return string
	 */
	public function getDescription(): string;

	/**
	 * Returns the command arguments.
	 *
	 * @return array
	 */
	public function getArguments(): array;
}
