<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator;

use mako\utility\Arr;
use RuntimeException;
use Throwable;

use function array_map;
use function implode;
use function mb_convert_case;
use function mb_substr;
use function rtrim;

/**
 * Validation exception.
 *
 * @author Frederic G. Østby
 */
class ValidationException extends RuntimeException
{
	/**
	 * Validation errors.
	 *
	 * @var array
	 */
	protected $errors;

	/**
	 * Exception meta.
	 *
	 * @var array
	 */
	protected $meta = [];

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

		$errors = implode(', ', array_map(function($value)
		{
			return rtrim(mb_convert_case(mb_substr($value, 0, 1), MB_CASE_LOWER) . mb_substr($value, 1), '.');
		}, $this->errors));

		return "{$message}: {$errors}.";
	}

	/**
	 * Adds meta.
	 *
	 * @param string $key   Meta key
	 * @param mixed  $value Meta value
	 */
	public function addMeta(string $key, $value): void
	{
		Arr::set($this->meta, $key, $value);
	}

	/**
	 * Gets meta.
	 *
	 * @param  string $key     Meta key
	 * @param  mixed  $default Default return value
	 * @return mixed
	 */
	public function getMeta(string $key, $default = null)
	{
		return Arr::get($this->meta, $key, $default);
	}
}
