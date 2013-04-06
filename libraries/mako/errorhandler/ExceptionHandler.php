<?php

namespace mako\errorhandler;

use \Exception;
use \ErrorException;
use \mako\Log;
use \mako\View;
use \mako\Config;
use \mako\Database;
use \mako\Response;
use \mako\reactor\CLI;

/**
 * Exception handler.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class ExceptionHandler
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Exception.
	 * 
	 * @var Exception
	 */

	protected $exception;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   Exception  $exception  Exception to handle
	 */

	public function __construct(Exception $exception)
	{
		$this->exception = $exception;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns the code that triggered the error.
	 * 
	 * @access  protected
	 * @param   string     $file     File where the error was triggered
	 * @param   int        $line     Line where the error was triggered
	 * @param   int        $padding  (optional) Lines of padding
	 * @return  string
	 */

	protected function getCode($file, $line, $padding = 5)
	{
		if(!is_readable($file))
		{
			return;
		}

		$handle      = fopen($file, 'r');
		$code        = array();
		$currentLine = 0;

		while(!feof($handle))
		{
			$currentLine++;

			$temp = fgets($handle);

			if($currentLine > $line + $padding)
			{
				break; // Exit loop after we have found what we were looking for
			}

			if($currentLine >= ($line - $padding) && $currentLine <= ($line + $padding))
			{
				$code[] = array('current' => $currentLine, 'line' => $temp);
			}
		}

		fclose($handle);

		return array('start' => ($line - $padding), 'code' => $code);
	}

	/**
	 * Modifies the backtrace array.
	 *
	 * @access  protected
	 * @param   array      $backtrace  Array returned by the getTrace() method of an exception object
	 * @return  array
	 */

	protected function formatBacktrace($backtrace)
	{
		if(is_array($backtrace) === false || count($backtrace) === 0)
		{
			return $backtrace;
		}
		
		// Remove unnecessary info from backtrace
		
		if($backtrace[0]['function'] == '{closure}')
		{
			unset($backtrace[0]);
		}
		
		// Format backtrace

		$trace = array();

		foreach($backtrace as $entry)
		{
			// Function
			
			$function = '';
			
			if(isset($entry['class']))
			{
				$function .= $entry['class'] . $entry['type'];
			}
			
			$function .= $entry['function'] . '()';
			
			// Arguments
			
			$arguments = array();
			
			if(isset($entry['args']) && count($entry['args']) > 0)
			{
				foreach($entry['args'] as $arg)
				{
					ob_start();

					var_dump($arg);

					$arg = htmlspecialchars(ob_get_contents());

					ob_end_clean();

					$arguments[] = $arg;
				}
			}
			
			// Location
			
			$location = array();
			
			if(isset($entry['file']))
			{
				$location['file']   = $entry['file'];
				$location['line']   = $entry['line'];
				$location['source'] = $this->getCode($entry['file'], $entry['line'], 0);
			}
			
			// Compile into array
			
			$trace[] = array
			(
				'function'  => $function,
				'arguments' => $arguments,
				'location'  => $location,
			);
		}

		return $trace;
	}

	/**
	 * Displays the error in a CLI friendly format.
	 * 
	 * @access  protected
	 * @param   array      $error  Error info
	 */

	protected function displayCLI($error)
	{
		$cli = new CLI();

		$code = $this->getCode($error['file'], $error['line'], 0);

		if(!empty($code))
		{
			$code = trim($code['code'][0]['line']);
		}
		else
		{
			$code = 'Unable to retrieve source code';
		}

		$output  = $error['type'] . PHP_EOL . PHP_EOL;
		$output .= $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line'] . PHP_EOL . PHP_EOL;
		$output .= str_repeat('-', $cli->screenWidth()) . PHP_EOL . PHP_EOL;
		$output .=  '    ' . $code . PHP_EOL . PHP_EOL;
		$output .= str_repeat('-', $cli->screenWidth()) . PHP_EOL . PHP_EOL;
		$output .= $this->exception->getTraceAsString() . PHP_EOL;

		$cli->stderr($output);
	}

	/**
	 * Displays the error in a web friendly format.
	 * 
	 * @access  protected
	 * @param   array      $error  Error info
	 */

	protected function displayWeb($error)
	{
		if(Config::get('application.error_handler.display_errors') === true)
		{
			$error['source'] = $this->getCode($error['file'], $error['line']);

			$error['queries'] = Database::getLog();

			$backtrace = $this->exception->getTrace();

			if($this->exception instanceof ErrorException)
			{
				$backtrace = array_slice($backtrace, 1); //Remove call to error handler from backtrace
			}

			$error['backtrace'] = $this->formatBacktrace($backtrace);

			Response::factory(new View('_mako_.errors.exception', array('error' => $error)))->send(500);
		}
		else
		{
			Response::factory(new View('_mako_.errors.error'))->send(500);
		}
	}

	/**
	 * Displays the error.
	 * 
	 * @access  protected
	 * @param   array      $error  Error info
	 */

	protected function display($error)
	{
		if(strtolower(PHP_SAPI) === 'cli')
		{
			$this->displayCLI($error);
		}
		else
		{
			$this->displayWeb($error);
		}
	}

	/**
	 * Handles the exception.
	 * 
	 * @access  public
	 */

	public function handle()
	{
		// Get exception info

		$error = array
		(
			'code'    => $this->exception->getCode(),
			'message' => $this->exception->getMessage(),
			'file'    => $this->exception->getFile(),
			'line'    => $this->exception->getLine(),
		);

		// Determine error type

		if($this->exception instanceof ErrorException)
		{
			$error['type'] = 'ErrorException: ';

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

			$error['type'] .= in_array($error['code'], array_keys($codes)) ? $codes[$error['code']] : 'Unknown Error';
		}
		else
		{
			$error['type'] = get_class($this->exception);
		}

		// Write to error log

		if(Config::get('application.error_handler.log_errors') === true)
		{
			Log::error("{$error['type']}: {$error['message']} in {$error['file']} at line {$error['line']}");
		}

		// Display error

		$this->display($error);
	}
}

/** -------------------- End of file --------------------**/