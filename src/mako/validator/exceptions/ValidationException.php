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
	 * Input.
	 */
	protected null|InputInterface $input = null;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected array $errors,
		string $message = '',
		int $code = 0, ?Throwable $previous = null
	)
	{
		parent::__construct($message, $code, $previous);
	}

	/**
	 * Returns the validation errors.
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}

	/**
	 * Returns the exception message along with the validation errors.
	 */
	public function getMessageWithErrors(): string
	{
		$message = rtrim($this->message, '.');

		$errors = implode(', ', array_map(static fn ($value) => rtrim(mb_convert_case(mb_substr($value, 0, 1), MB_CASE_LOWER) . mb_substr($value, 1), '.'), $this->errors));

		return "{$message}: {$errors}.";
	}

	/**
	 * Sets the input.
	 */
	public function setInput(InputInterface $input): void
	{
		$this->input = $input;
	}

	/**
	 * Returns the input.
	 */
	public function getInput(): ?InputInterface
	{
		return $this->input;
	}
}
