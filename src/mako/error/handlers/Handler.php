<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\error\handlers;

use \Exception;
use \ErrorException;
use \Psr\Log\LoggerInterface;

/**
 * Base handler.
 * 
 * @author  Frederic G. Østby
 */

abstract class Handler implements \mako\error\handlers\HandlerInterface
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Exception.
	 * 
	 * @var \Exception
	 */

	protected $exception;

	/**
	 * Logger instance.
	 * 
	 * @var \Psr\Log\LoggerInterface
	 */

	protected $logger;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \Exception  $exception  Exception
	 */

	public function __construct(Exception $exception)
	{
		$this->exception = $exception;
	}

	/**
	 * Destructor.
	 * 
	 * @access  public
	 */

	public function __destruct()
	{
		if(!empty($this->logger))
		{
			$this->logger->error($this->exception);
		}
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Set logger instance.
	 * 
	 * @var \Psr\Log\LoggerInterface
	 */

	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
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
			$codes = array
			(
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
			);

			return in_array($code, array_keys($codes)) ? $codes[$code] : 'ErrorException';
		}
		else
		{
			return get_class($exception);
		}
	}
}