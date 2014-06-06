<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\error;

use \Closure;
use \ErrorException;
use \Exception;

use \mako\error\handlers\ExceptionHandler;

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
	 * @param   string    $exception  Exception type
	 * @param   \Closure  $handler    Exception handler
	 */

	public function handle($exception, Closure $handler)
	{
		array_unshift($this->handlers, compact('exception', 'handler'));
	}

	/**
	 * Clears all error handlers for an exception type.
	 * 
	 * @access  public
	 * @param   string  $exception  Exception type
	 */

	public function clearHandlers($exception)
	{
		foreach($this->handlers as $key => $handler)
		{
			if($handler['exception'] === $exception)
			{
				unset($this->handlers[$key]);
			}
		}
	}

	/**
	 * Replaces all error handlers for an exception type with a new one.
	 * 
	 * @access  public
	 * @param   string    $exception  Exception type
	 * @param   \Closure  $handler    Exception handler
	 */

	public function replaceHandlers($exception, Closure $handler)
	{
		$this->clearHandlers($exception);

		$this->handle($exception, $handler);
	}

	/**
	 * Handles uncaught exceptions.
	 *
	 * @access  public
	 * @param   Exception  $exception  An exception object
	 */

	public function handler($exception)
	{
		try
		{
			// Empty output buffers

			while(ob_get_level() > 0) ob_end_clean();

			// Loop through the exception handlers

			foreach($this->handlers as $handler)
			{
				if($exception instanceof $handler['exception'])
				{
					if(($return = $handler['handler']($exception)) !== null)
					{
						break;
					}
				}
			}
		}
		catch(Exception $e)
		{
			while(ob_get_level() > 0) ob_end_clean();

			echo $e->getMessage() . ' on line [ ' . $e->getLine() . ' ] in [ ' . $e->getFile() . ' ]'; 
		}

		exit(1);
	}
}