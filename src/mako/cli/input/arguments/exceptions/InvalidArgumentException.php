<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\arguments\exceptions;

use Throwable;

/**
 * Invalid option exception.
 *
 * @author Frederic G. Østby
 */
class InvalidArgumentException extends ArgumentException
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
	 * @param string|null     $suggestion Suggestion
	 * @param int             $code       The Exception code
	 * @param \Throwable|null $previous   The previous exception used for the exception chaining
	 */
	public function __construct(string $message, ?string $suggestion = null, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);

		$this->suggestion = $suggestion;
	}

	/**
	 * Returns a argument name suggestion.
	 *
	 * @return string|null
	 */
	public function getSuggestion(): ?string
	{
		return $this->suggestion;
	}

	/**
	 * Returns the exception message with a suggestion.
	 *
	 * @return string
	 */
	public function getMessageWithSuggestion(): string
	{
		$message = $this->getMessage();

		if($this->suggestion !== null)
		{
			$message .= " Did you mean [ {$this->suggestion} ]?";
		}

		return $message;
	}
}
