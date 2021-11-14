<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use Throwable;

/**
 * Not found exception.
 */
class NotFoundException extends HttpStatusException
{
	/**
	 * {@inheritDoc}
	 */
	protected $defaultMessage = 'The resource you requested could not be found. It may have been moved or deleted.';

	/**
	 * Constructor.
	 *
	 * @param string          $message  Exception message
	 * @param \Throwable|null $previous Previous exception
	 */
	public function __construct(string $message = '', ?Throwable $previous = null)
	{
		parent::__construct(404, $message, $previous);
	}
}
