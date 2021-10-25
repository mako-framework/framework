<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use Throwable;

/**
 * Method not allowed exception.
 *
 * @author Frederic G. Østby
 */
class MethodNotAllowedException extends HttpException
{
	/**
	 * {@inheritDoc}
	 */
	protected $defaultMessage = 'The request method that was used is not supported by this resource.';

	/**
	 * Allowed methods.
	 *
	 * @var array
	 */
	protected $allowedMethods;

	/**
	 * Constructor.
	 *
	 * @param array           $allowedMethods Allowed methods
	 * @param string          $message        Exception message
	 * @param \Throwable|null $previous       Previous exception
	 */
	public function __construct(array $allowedMethods = [], string $message = '', ?Throwable $previous = null)
	{
		$this->allowedMethods = $allowedMethods;

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
