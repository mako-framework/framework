<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\error;

use Closure;
use ErrorException;
use Exception;

use Psr\Log\LoggerInterface;

/**
 * Error handler.
 *
 * @author  Frederic G. Østby
 */

class ErrorHandler
{
	/**
	 * Is the shutdown handler disabled?
	 *
	 * @var boolean
	 */

	protected $disableShutdownHandler = false;

	/**
	 * Exception handlers.
	 *
	 * @var array
	 */

	protected $handlers = [];

	/**
	 * Logger instance.
	 *
	 * @var \Psr\Log\LoggerInterface
	 */

	protected $logger;

	/**
	 * Exception types that shouldn't be logged.
	 *
	 * @var array
	 */

	protected $disableLoggingFor = [];

	/**
	 * Constructor.
	 *
	 * @access  public
	 */

	public function __construct()
	{
		// Add a basic exception handler to the stack

		$this->handle('\Exception', function($e)
		{
			echo '[ ' . get_class($e) . '] ' . $e->getMessage() . ' on line [ ' . $e->getLine() . ' ] in [ ' . $e->getFile() . ' ]';

			echo PHP_EOL;

			echo $e->getTraceAsString();
		});

		// Registers the exception handler

		$this->register();
	}

	/**
	 * Registers the exception handler.
	 *
	 * @access  protected
	 */

	protected function register()
	{
		// Allows us to handle "fatal" errors

		register_shutdown_function(function()
		{
			$e = error_get_last();

			if($e !== null && (error_reporting() & $e['type']) !== 0 && !$this->disableShutdownHandler)
			{
				$this->handler(new ErrorException($e['message'], $e['type'], 0, $e['file'], $e['line']));

				exit(1);
			}
		});

		// Set the exception handler

		set_exception_handler([$this, 'handler']);
	}

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
	 * Disables logging for an exception type.
	 *
	 * @access  public
	 * @param   string|array  $exceptionType  Exception type or array of exception types
	 */

	public function disableLoggingFor($exceptionType)
	{
		$this->disableLoggingFor = array_unique(array_merge($this->disableLoggingFor, (array) $exceptionType));
	}

	/**
	 * Disables the shutdown handler.
	 *
	 * @access  public
	 */

	public function disableShutdownHandler()
	{
		$this->disableShutdownHandler = true;
	}

	/**
	 * Prepends an exception handler to the stack.
	 *
	 * @access  public
	 * @param   string    $exceptionType  Exception type
	 * @param   \Closure  $handler        Exception handler
	 */

	public function handle($exceptionType, Closure $handler)
	{
		array_unshift($this->handlers, compact('exceptionType', 'handler'));
	}

	/**
	 * Clears all error handlers for an exception type.
	 *
	 * @access  public
	 * @param   string  $exceptionType  Exception type
	 */

	public function clearHandlers($exceptionType)
	{
		foreach($this->handlers as $key => $handler)
		{
			if($handler['exceptionType'] === $exceptionType)
			{
				unset($this->handlers[$key]);
			}
		}
	}

	/**
	 * Replaces all error handlers for an exception type with a new one.
	 *
	 * @access  public
	 * @param   string    $exceptionType  Exception type
	 * @param   \Closure  $handler        Exception handler
	 */

	public function replaceHandlers($exceptionType, Closure $handler)
	{
		$this->clearHandlers($exceptionType);

		$this->handle($exceptionType, $handler);
	}

	/**
	 * Clear output buffers.
	 *
	 * @access  protected
	 */

	protected function clearOutputBuffers()
	{
		while(ob_get_level() > 0) ob_end_clean();
	}

	/**
	 * Should the exception be logged?
	 *
	 * @access  public
	 * @param   \Exception  $exception  An exception object
	 * @return  boolean
	 */

	protected function shouldExceptionBeLogged($exception)
	{
		if($this->logger === null)
		{
			return false;
		}

		foreach($this->disableLoggingFor as $exceptionType)
		{
			if($exception instanceof $exceptionType)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Handles uncaught exceptions.
	 *
	 * @access  public
	 * @param   \Exception  $exception  An exception object
	 */

	public function handler($exception)
	{
		try
		{
			// Empty output buffers

			$this->clearOutputBuffers();

			// Loop through the exception handlers

			foreach($this->handlers as $handler)
			{
				if($exception instanceof $handler['exceptionType'])
				{
					if($handler['handler']($exception) !== null)
					{
						break;
					}
				}
			}

			// Log exception

			if($this->shouldExceptionBeLogged($exception))
			{
				$this->logger->error($exception);
			}
		}
		catch(Exception $e)
		{
			// Empty output buffers

			$this->clearOutputBuffers();

			// One of the exception handlers failed so we'll just show the user a generic error screen

			echo $e->getMessage() . ' on line [ ' . $e->getLine() . ' ] in [ ' . $e->getFile() . ' ]' . PHP_EOL;
		}

		exit(1);
	}
}