<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use Throwable;

/**
 * Gone exception.
 *
 * @author Frederic G. Østby
 */
class GoneException extends HttpException
{
	/**
	 * {@inheritdoc}
	 */
	protected $defaultMessage = 'The resource you requested is no longer available and will not be available again.';

	/**
	 * Constructor.
	 *
	 * @param string          $message  Exception message
	 * @param \Throwable|null $previous Previous exception
	 */
	public function __construct(string $message = '', ?Throwable $previous = null)
	{
		parent::__construct(410, $message, $previous);
	}
}
