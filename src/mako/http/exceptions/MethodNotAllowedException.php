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
	 * Allowed methods.
	 *
	 * @var array
	 */
	protected $allowedMethods;

	/**
	 * Constructor.
	 *
	 * @param array           $allowedMethods Allowed methods
	 * @param string|null     $message        Exception message
	 * @param \Throwable|null $previous       Previous exception
	 */
	public function __construct(array $allowedMethods = [], ?string $message = null, ?Throwable $previous = null)
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
