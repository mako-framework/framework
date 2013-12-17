<?php

namespace mako\core\errors;

use \Closure;
use \Exception;
use \ErrorException;
use \mako\core\errors\handlers\ExceptionHandler;

/**
 * Error handler.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class ErrorHandler
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Exception handlers.
	 * 
	 * @var array
	 */

	protected static $handlers = [];

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------
	
	/**
	 * Protected constructor since this is a static class.
	 *
	 * @access  protected
	 */
	
	protected function __construct()
	{
		// Nothing here
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Registers the exception handler.
	 * 
	 * @access  public
	 */

	public static function register()
	{
		// Allows us to handle "fatal" errors

		register_shutdown_function(function()
		{
			$e = error_get_last();
			
			if($e !== null && (error_reporting() & $e['type']) !== 0 && !defined('MAKO_DISABLE_FATAL_ERROR_HANDLER'))
			{
				ErrorHandler::handler(new ErrorException($e['message'], $e['type'], 0, $e['file'], $e['line']));

				exit(1);
			}
		});

		// Set the exception handler
		
		set_exception_handler(function($e)
		{
			ErrorHandler::handler($e);
		});

		// Registers the default exception handler

		static::handle('\Exception', function($exception)
		{
			$handler = new ExceptionHandler($exception);

			$handler->handle();
		});
	}

	/**
	 * Prepends an exception handler to the stack.
	 * 
	 * @access  public
	 * @param   string    $exception  Exception type
	 * @param   \Closure  $handler    Exception handler
	 */

	public static function handle($exception, Closure $handler)
	{
		array_unshift(static::$handlers, compact('exception', 'handler'));
	}

	/**
	 * Clears all error handlers for an exception type.
	 * 
	 * @access  public
	 * @param   string  $exception  Exception type
	 */

	public static function clearHandlers($exception)
	{
		foreach(static::$handlers as $key => $handler)
		{
			if($handler['exception'] === $exception)
			{
				unset(static::$handlers[$key]);
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

	public static function replaceHandlers($exception, Closure $handler)
	{
		static::clearHandlers($exception);

		static::handle($exception, $handler);
	}

	/**
	 * Handles uncaught exceptions.
	 *
	 * @access  public
	 * @param   Exception  $exception  An exception object
	 */

	public static function handler($exception)
	{
		try
		{
			// Empty output buffers

			while(ob_get_level() > 0) ob_end_clean();

			// Loop through the exception handlers

			foreach(static::$handlers as $handler)
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

			echo $e->getMessage() . ' in ' . $e->getFile() . ' (line ' . $e->getLine() . ').';
		}

		exit(1);
	}
}

/** -------------------- End of file -------------------- **/