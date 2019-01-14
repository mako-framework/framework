<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use Throwable;

/**
 * Bad request exception.
 *
 * @author Frederic G. Østby
 */
class BadRequestException extends HttpException
{
	/**
	 * {@inheritdoc}
	 */
	protected $defaultMessage = 'The server was unable to process the request.';

	/**
	 * Constructor.
	 *
	 * @param string          $message  Exception message
	 * @param \Throwable|null $previous Previous exception
	 */
	public function __construct(string $message = '', ?Throwable $previous = null)
	{
		parent::__construct(400, $message, $previous);
	}
}
