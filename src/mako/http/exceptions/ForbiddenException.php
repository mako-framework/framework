<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use Throwable;

/**
 * Forbidden exception.
 */
class ForbiddenException extends HttpException
{
	/**
	 * {@inheritdoc}
	 */
	protected $defaultMessage = 'You don\'t have permission to access the requested resource.';

	/**
	 * Constructor.
	 *
	 * @param string          $message  Exception message
	 * @param \Throwable|null $previous Previous exception
	 */
	public function __construct(string $message = '', ?Throwable $previous = null)
	{
		parent::__construct(403, $message, $previous);
	}
}
