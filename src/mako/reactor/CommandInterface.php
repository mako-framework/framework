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
	 * Incorrect usage status code.
	 */
	public const int STATUS_INCORRECT_USAGE = 2;

	/**
	 * Unknown command status code.
	 */
	public const int STATUS_UNKNOWN_COMMAND = 127;
}
