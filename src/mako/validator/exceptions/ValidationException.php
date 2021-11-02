<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\exceptions;

use mako\validator\input\InputInterface;
use Throwable;

use function array_map;
use function implode;
use function mb_convert_case;
use function mb_substr;
use function rtrim;

/**
 * Validation exception.
 */
class ValidationException extends ValidatorException
{
	/**
	 * Validation errors.
	 *
	 * @var array
	 */
	protected $errors;

	/**
	 * Input.
	 *
	 * @var \mako\validator\input\InputInterface|null
	 */
	protected $input;

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

	/**
	 * Returns the exception message along with the validation errors.
	 *
	 * @return string
	 */
	public function getMessageWithErrors(): string
	{
		$message = rtrim($this->message, '.');

		$errors = implode(', ', array_map(static fn($value) => rtrim(mb_convert_case(mb_substr($value, 0, 1), MB_CASE_LOWER) . mb_substr($value, 1), '.'), $this->errors));

		return "{$message}: {$errors}.";
	}

	/**
	 * Sets the input.
	 *
	 * @param \mako\validator\input\InputInterface $input Input
	 */
	public function setInput(InputInterface $input): void
	{
		$this->input = $input;
	}

	/**
	 * Returns the input.
	 *
	 * @return \mako\validator\input\InputInterface|null
	 */
	public function getInput(): ?InputInterface
	{
		return $this->input;
	}
}
