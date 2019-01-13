<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use Throwable;

/**
 * Invalid token exception.
 *
 * @author Frederic G. Østby
 */
class InvalidTokenException extends HttpException
{
	/**
	 * {@inheritdoc}
	 */
	protected $defaultMessage = 'An invalid or expired token was provided.';

	/**
	 * Constructor.
	 *
	 * @param string          $message  Exception message
	 * @param \Throwable|null $previous Previous exception
	 */
	public function __construct(string $message = '', ?Throwable $previous = null)
	{
		parent::__construct(498, $message, $previous);
	}
}
