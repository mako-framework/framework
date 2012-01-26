<?php

namespace mako
{
	use \mako\Log;
	use \mako\UTF8;
	use \mako\View;
	use \mako\Cache;
	use \mako\Request;
	use \mako\Response;
	use \Exception;
	use \ErrorException;
	use \RuntimeException;
	use \ArrayObject;

	// Setup class alias to maintain backwards compability

	class_alias('\mako\Mako', 'Mako');

	/**
	* Class containing core methods that are used throughout the framework.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2012 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class Mako
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------
		
		/**
		* Mako version number
		*/
		
		const VERSION = '1.6.1';
		
		/**
		* Array holding all config variables
		*/
		
		protected static $config = array();
		
		/**
		* Does the config cache need to be updated?
		*/
		
		protected static $updateCache = false;
		
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
		* Initializes the system.
		*
		* @access  protected
		*/
		
		protected static function init()
		{
			// Start output buffering
			
			ob_start();

			// Include bootstrap file
			
			require MAKO_APPLICATION . '/bootstrap.php';
			
			// Load config
			
			if(MAKO_INTERNAL_CACHE === true)
			{
				$cache = Cache::instance()->read(MAKO_APPLICATION_ID . '_config');
				
				if($cache !== false)
				{
					static::$config = $cache;
				}
			}
			
			if(isset(static::$config['mako']) === false)
			{
				static::config('mako');	
			}
			
			// Setup error handling
			
			if(static::$config['mako']['error_handler']['enable'] === true)
			{
				set_error_handler('\mako\Mako::errorHandler');
				
				register_shutdown_function('\mako\Mako::fatalErrorHandler');
				
				set_exception_handler('\mako\Mako::exceptionHandler');
			}
			
			// Send default header and set internal encoding
			
			header('Content-Type: text/html; charset=UTF-8');
			
			mb_language('uni');
			mb_regex_encoding('UTF-8');
			mb_internal_encoding('UTF-8');
			
			// Clean input
		
			$_GET     = static::cleanInput($_GET);
			$_POST    = static::cleanInput($_POST);
			$_COOKIE  = static::cleanInput($_COOKIE);
			$_REQUEST = static::cleanInput($_REQUEST);
			$_SERVER  = static::cleanInput($_SERVER, false);
					
			// Set default timezone
			
			date_default_timezone_set(static::$config['mako']['timezone']);
			
			// Set locale
			
			static::locale(static::$config['mako']['locale']['locales'], static::$config['mako']['locale']['lc_numeric']);

			// Initialize bundles

			foreach(static::$config['mako']['bundles'] as $bundle)
			{
				static::bundle($bundle);
			}
		}
		
		/**
		* Performs tasks that need to be done before sending output.
		*
		* @access  protected
		*/
		
		protected static function cleanup()
		{
			// Save or update config cache if needed
			
			if(MAKO_INTERNAL_CACHE === true && static::$updateCache === true)
			{
				Cache::instance()->write(MAKO_APPLICATION_ID . '_config', static::$config, 3600);
			}
		}
		
		/**
		* Executes request and sends response.
		*
		* @access  public
		* @param   string  (optional) URL segments passed to the request handler.
		*/
		
		public static function run($route = null)
		{
			static::init();

			Request::factory($route)->execute();
				
			static::cleanup();

			Response::instance()->send();
		}
		
		/**
		* Removes malformed utf-8 and strips slashes added by magic quotes.
		*
		* @access  protected
		* @param   mixed    String or array to clean
		* @param   boolean  (optional) True to strip slashes and false to leave them
		* @return  mixed
		*/
		
		protected static function cleanInput($input, $stripSlashes = true)
		{
			if(is_array($input))
			{
				foreach($input as $key => $value)
				{
					$input[$key] = static::cleanInput($value, $stripSlashes);
				}
			}
			else
			{
				// Removes malformed UTF-8 characters
				
				if(UTF8::isAscii($input) === false)
				{
					$input = UTF8::clean($input);
				}
							
				// Remove slashes added by "magic_quotes"

				if(MAKO_MAGIC_QUOTES === 1 && $stripSlashes === true)
				{
					$input = stripslashes($input);
				}
			}
			
			return $input;
		}
		
		/**
		* Returns a config group.
		*
		* @access  public
		* @param   string  Name of the config file
		* @return  array
		*/
		
		public static function config($file)
		{
			if(isset(static::$config[$file]) === false)
			{
				if(strrpos($file, '::') !== false)
				{
					list($bundle, $file) = explode('::', $file);

					$path = MAKO_BUNDLES . '/' . $bundle . '/config/' . $file . '.php';
				}
				else
				{
					$path = MAKO_APPLICATION . '/config/' . $file . '.php';
				}
				
					
				if(file_exists($path) === false)
				{
					throw new RuntimeException(vsprintf("%s(): The '%s' config file does not exist.", array(__METHOD__, $file)));
				}	

				static::$config[$file] = new ArrayObject(include($path), ArrayObject::ARRAY_AS_PROPS);
					
				if($file !== 'cache')
				{
					static::$updateCache = true;
				}
			}
				
			return static::$config[$file];
		}

		/**
		* Initialize bundle.
		*
		* @access  public
		* @param   string  Budle name
		*/

		public static function bundle($bundle)
		{
			$file = MAKO_BUNDLES . '/' . $bundle . '/_init.php';

			if(!file_exists($file))
			{
				throw new RuntimeException(vsprintf("%s(): Unable to initialize the '%s' bundle. Make sure that it has been installed.", array(__METHOD__, $bundle)));
			}

			include_once $file;
		}
			
		
		/**
		* Set locale information.
		*
		* @access  public
		* @param   mixed    (optional) Locale or array of locales to try until success
		* @param   boolean  (optional) Set to false to use the 'C' locale for LC_NUMERIC
		*/
		
		public static function locale($locale = null, $numeric = false)
		{
			if($locale !== null)
			{	
				setlocale(LC_ALL, $locale);
				
				if($numeric === false)
				{
					setlocale(LC_NUMERIC, 'C');
				}
			}
		}
		
		/**
		* Returns a mako framework url.
		*
		* @access  public
		* @param   string   URL segments
		* @param   array    (optional) Associative array used to build URL-encoded query string
		* @param   string   (optional) Argument separator
		* @return  string
		*/
		
		public static function url($route = '', array $params = null, $separator = '&amp;')
		{	
			if($route === '')
			{
				$url = static::$config['mako']['base_url'];
			}
			else
			{
				$url = static::$config['mako']['clean_urls'] === true ? 
				       static::$config['mako']['base_url'] . '/' . $route : 
				       static::$config['mako']['base_url'] . '/index.php/' . $route;
			}
			
			if($params !== null)
			{
				$url .= '?' . http_build_query($params, '', $separator);
			}
			
			return $url;
		}
		
		/**
		* Returns an array of lines from a file.
		*
		* @access  public
		* @param   string   File in which you want to highlight a line
		* @param   int      Line number to highlight
		* @param   int      (optional) Number of padding lines
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
				$search  = array("\n", '<code>', '</code>', '<span style="color: #0000BB">&lt;?php&nbsp;', '#$@r4!/*');
				$replace = array('', '', '', '<span style="color: #0000BB">', '/*');

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
		* Returns a backtrace array.
		*
		* @access  protected
		* @param   array    Array returned by the getTrace() method of an exception object
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
		* Converts errors to ErrorExceptions.
		*
		* @access  public
		* @param   int      The error code
		* @param   str      The error message
		* @param   str      The filename where the error occurred
		* @param   int      The line number where the error occurred
		* @return  boolean
		*/

		public static function errorHandler($code, $message, $file, $line)
		{
			if((error_reporting() & $code) !== 0)
			{
				throw new ErrorException($message, $code, 0, $file, $line);
			}

			// Don't execute PHP internal error handler

			return true;
		}
		
		/**
		* Convert errors not caught by the errorHandler to ErrorExceptions.
		*
		* @access  public
		*/

		public static function fatalErrorHandler()
		{
			$error = error_get_last();
			
			if($error !== null && (error_reporting() & $error['type']) !== 0)
			{
				try
				{
					static::exceptionHandler(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
				}
				catch(Exception $e)
				{
					while(ob_get_level() > 0) ob_end_clean();

					echo $e->getMessage() . ' in ' . $e->getFile() . ' (line ' . $e->getLine() . ').';
				}

				exit(1);
			}
		}
		
		/**
		* Handles uncaught exceptions and returns a pretty error screen.
		*
		* @access  public
		* @param   Exception  An exception object
		*/

		public static function exceptionHandler($exception)
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

				// Write to error log (disabled for E_COMPILE_ERROR and E_ERROR as it causes problems with autoloader)

				if(static::$config['mako']['error_handler']['log_errors'] === true && !in_array($error['code'], array(E_COMPILE_ERROR, E_ERROR)))
				{
					Log::instance()->write("{$error['type']}: {$error['message']} in {$error['file']} at line {$error['line']}");
				}

				// Send headers and output

				@header('Content-Type: text/html; charset=UTF-8');

				@Response::instance()->status(500);

				if(static::$config['mako']['error_handler']['display_errors'] === true)
				{
					$error['backtrace'] = $exception->getTrace();

					if($exception instanceof ErrorException)
					{
						$error['backtrace'] = array_slice($error['backtrace'], 1); //Remove call to error handler from backtrace
					}

					$error['backtrace']   = static::formatBacktrace($error['backtrace']);
					$error['highlighted'] = static::highlightCode($error['file'], $error['line']);

					include MAKO_APPLICATION . '/views/_errors/exception.php'; // Include file instead of using view class as it can cause problems with fatal errors.
				}
				else
				{
					include MAKO_APPLICATION . '/views/_errors/error.php'; // Include file instead of using view class as it can cause problems with fatal errors.
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
}

/** -------------------- End of file --------------------**/