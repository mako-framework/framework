<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator;

use RuntimeException;
use Throwable;

/**
 * Validator exception.
 *
 * @author Frederic G. Østby
 */
class ValidatorException extends RuntimeException
{
	/**
	 * Validation errors.
	 *
	 * @var array
	 */
	protected $errors;

	/**
	 * Constructor.
	 *
	 * @param array           $errors   Validation errors
	 * @param string          $message  Exception code
	 * @param int             $code     Exception message
	 * @param \Throwable|null $previous Previous exception
	 */
	public function __construct(array $errors, string $message = '', int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);

		$this->errors = $errors;
	}

	/**
	 * Returns the validation errors.
	 *
	 * @return array
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}
}
