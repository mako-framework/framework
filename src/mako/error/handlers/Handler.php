<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\error\handlers;

use Exception;
use ErrorException;

use mako\error\handlers\HandlerInterface;

/**
 * Base handler.
 *
 * @author  Frederic G. Østby
 */

abstract class Handler implements HandlerInterface
{
	/**
	 * Exception.
	 *
	 * @var \Exception
	 */

	protected $exception;

	/**
	 * {@inheritdoc}
	 */

	public function __construct(Exception $exception)
	{
		$this->exception = $exception;
	}

	/**
	 * Determines the exception type.
	 *
	 * @access  protected
	 * @param   \Exception  $exception  Exception
	 * @return  string
	 */

	protected function determineExceptionType($exception)
	{
		$code = $exception->getCode();

		if($exception instanceof ErrorException)
		{
			$codes =
			[
				E_ERROR             => 'Fatal Error',
				E_PARSE             => 'Parse Error',
				E_COMPILE_ERROR     => 'Compile Error',
				E_COMPILE_WARNING   => 'Compile Warning',
				E_STRICT            => 'Strict Mode Error',
				E_NOTICE            => 'Notice',
				E_WARNING           => 'Warning',
				E_RECOVERABLE_ERROR => 'Recoverable Error',
				E_DEPRECATED        => 'Deprecated',
				E_USER_NOTICE       => 'Notice',
				E_USER_WARNING      => 'Warning',
				E_USER_ERROR        => 'Error',
				E_USER_DEPRECATED   => 'Deprecated'
			];

			return in_array($code, array_keys($codes)) ? $codes[$code] : 'ErrorException';
		}
		else
		{
			return get_class($exception);
		}
	}
}