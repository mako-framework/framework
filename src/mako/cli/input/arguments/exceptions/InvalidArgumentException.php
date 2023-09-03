<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\arguments\exceptions;

use Throwable;

/**
 * Invalid option exception.
 */
class InvalidArgumentException extends ArgumentException
{
	/**
	 * Constructor.
	 */
	public function __construct(string $message, ?string $suggestion = null, int $code = 0, ?Throwable $previous = null)
	{
		if($suggestion !== null)
		{
			$message .= " Did you mean [ {$suggestion} ]?";
		}

		parent::__construct($message, $code, $previous);
	}
}
