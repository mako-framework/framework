<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\reactor\exceptions;

use Throwable;

/**
 * Invalid option exception.
 *
 * @author Frederic G. Østby
 */
class InvalidOptionException extends ArgumentException
{
	/**
	 * Suggestion.
	 *
	 * @var string
	 */
	protected $suggestion;

	/**
	 * Constructor.
	 *
	 * @param string          $message    The Exception message to throw
	 * @param string          $name       Argument name
	 * @param string|null     $suggestion Suggestion
	 * @param int             $code       The Exception code
	 * @param \Throwable|null $previous   The previous exception used for the exception chaining
	 */
	public function __construct(string $message, string $name, string $suggestion = null, int $code = 0, Throwable $previous = null)
	{
		parent::__construct($message, $name, $code, $previous);

		$this->suggestion = $suggestion;
	}

	/**
	 * Returns a argument name suggestion.
	 *
	 * @return string|null
	 */
	public function getSuggestion()
	{
		return $this->suggestion;
	}
}
