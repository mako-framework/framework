<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use Throwable;

/**
 * Too many requests exception.
 */
class UnsupportedMediaTypeException extends HttpStatusException
{
	/**
	 * {@inheritDoc}
	 */
	protected $defaultMessage = 'The media type is not supported.';

	/**
	 * Constructor.
	 *
	 * @param string          $message  Exception message
	 * @param \Throwable|null $previous Previous exception
	 */
	public function __construct(string $message = '', ?Throwable $previous = null)
	{
		parent::__construct(415, $message, $previous);
	}
}
