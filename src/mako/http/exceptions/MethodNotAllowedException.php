<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use Throwable;

/**
 * Method not allowed exception.
 */
class MethodNotAllowedException extends HttpStatusException
{
	/**
	 * {@inheritDoc}
	 */
	protected $defaultMessage = 'The request method that was used is not supported by this resource.';

	/**
	 * Constructor.
	 *
	 * @param array           $allowedMethods Allowed methods
	 * @param string          $message        Exception message
	 * @param \Throwable|null $previous       Previous exception
	 */
	public function __construct(
		protected array $allowedMethods = [],
		string $message = '',
		?Throwable $previous = null
	)
	{
		parent::__construct(405, $message, $previous);
	}

	/**
	 * Returns the allowed methods.
	 *
	 * @return array
	 */
	public function getAllowedMethods()
	{
		return $this->allowedMethods;
	}
}
