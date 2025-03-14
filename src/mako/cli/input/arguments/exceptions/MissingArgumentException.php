<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\arguments\exceptions;

use mako\cli\input\arguments\Argument;
use Throwable;

/**
 * Missing argument exception.
 */
class MissingArgumentException extends ArgumentException
{
	/**
	 * Constructor.
	 */
	public function __construct(
		string $message = '',
		int $code = 0,
		?Throwable $previous = null,
		protected ?Argument $argument = null
	) {
		parent::__construct($message, $code, $previous);
	}

	/**
	 * Returns the missing argument.
	 */
	public function getArgument(): ?Argument
	{
		return $this->argument;
	}

}
