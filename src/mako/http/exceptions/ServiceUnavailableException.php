<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use Throwable;

/**
 * Service unavailable exception.
 *
 * @author Frederic G. Østby
 */
class ServiceUnavailableException extends HttpException
{
	/**
	 * {@inheritDoc}
	 */
	protected $defaultMessage = 'The service is currently unavailable.';

	/**
	 * Constructor.
	 *
	 * @param string          $message  Exception message
	 * @param \Throwable|null $previous Previous exception
	 */
	public function __construct(string $message = '', ?Throwable $previous = null)
	{
		parent::__construct(503, $message, $previous);
	}
}
