<?php

namespace mako;

use \mako\Config;
use \mako\Log;
use \mako\Response;
use \Exception;
use \ErrorException;

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
	
	// Nothing here
	
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
	 * Returns an array of lines from a file.
	 *
	 * @access  public
	 * @param   string   $file     File in which you want to highlight a line
	 * @param   int      $line     Line number to highlight
	 * @param   int      $padding  (optional) Number of padding lines
	 * @return  array
	 */

	protected static function highlightCode($file, $line, $padding = 6)
	{
		if(!is_readable($file))
		{
			return false;
		}

		$highlight = function($string)
		{
			$search  = array("\r\n", "\n\r", "\r", "\n", '<code>', '</code>', '<span style="color: #0000BB">&lt;?php&nbsp;', '#$@r4!/*');
			$replace = array('', '', '', '', '', '', '<span style="color: #0000BB">', '/*');

			return str_replace($search, $replace, highlight_string('<?php ' . str_replace('/*', '#$@r4!/*', $string), true));	
		};

		$handle      = fopen($file, 'r');
		$lines       = array();
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
				$lines[] = array
				(
					'number'      => str_pad($currentLine, 4, ' ', STR_PAD_LEFT),
					'highlighted' => ($currentLine === $line),
					'code'        => $highlight($temp),
				);
			}
		}

		fclose($handle);

		return $lines;
	}
	
	/**
	 * Modifies the backtrace array.
	 *
	 * @access  protected
	 * @param   array      $backtrace  Array returned by the getTrace() method of an exception object
	 * @return  array
	 */

	protected static function formatBacktrace($backtrace)
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
				$location['file'] = $entry['file'];
				$location['line'] = $entry['line'];
				$location['code'] = static::highlightCode($entry['file'], $entry['line']);
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
	 * Handles uncaught exceptions and returns a pretty error screen.
	 *
	 * @access  public
	 * @param   Exception  $exception  An exception object
	 */

	public static function exception($exception)
	{
		try
		{
			// Empty output buffers

			while(ob_get_level() > 0) ob_end_clean();

			// Get exception info

			$error['code']    = $exception->getCode();
			$error['message'] = $exception->getMessage();
			$error['file']    = $exception->getFile();
			$error['line']    = $exception->getLine();

			// Determine error type

			if($exception instanceof ErrorException)
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
				$error['type'] = get_class($exception);
			}

			// Write to error log

			if(Config::get('application.error_handler.log_errors') === true)
			{
				Log::error("{$error['type']}: {$error['message']} in {$error['file']} at line {$error['line']}");
			}

			// Send headers and output
			
			if(Config::get('application.error_handler.display_errors') === true)
			{
				$error['backtrace'] = $exception->getTrace();

				if($exception instanceof ErrorException)
				{
					$error['backtrace'] = array_slice($error['backtrace'], 1); //Remove call to error handler from backtrace
				}

				$error['backtrace']   = static::formatBacktrace($error['backtrace']);
				$error['highlighted'] = static::highlightCode($error['file'], $error['line']);

				Response::factory(new View('_mako_.errors.exception', array('error' => $error)))->send(500);
			}
			else
			{
				Response::factory(new View('_mako_.errors.error'))->send(500);
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

/** -------------------- End of file --------------------**/