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

	/**
	* Core methods used throughout the framework.
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
		
		const VERSION = '1.6.0';
		
		/**
		* Are we running on Windows?
		*/
		
		protected static $isWindows;
		
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
			
			// Are we running on windows?
			
			static::$isWindows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
			
			// Define some constants

			define('MAKO_START', microtime(true));
			define('MAKO_MAGIC_QUOTES', get_magic_quotes_gpc());
			define('MAKO_APPLICATION', MAKO_APPLICATION_PATH . '/' . MAKO_APPLICATION_NAME);
			define('MAKO_APPLICATION_ID', md5(MAKO_APPLICATION));
			
			// Setup autoloading of classes
			
			spl_autoload_register('\mako\Mako::classLoader');
			
			// Include required files to speed things up a bit
			
			require MAKO_LIBRARIES_PATH . '/mako/Cache.php';
			require MAKO_LIBRARIES_PATH . '/mako/cache/Adapter.php';
			require MAKO_LIBRARIES_PATH . '/mako/UTF8.php';
			require MAKO_LIBRARIES_PATH . '/mako/Request.php';
			require MAKO_LIBRARIES_PATH . '/mako/Response.php';
			require MAKO_LIBRARIES_PATH . '/mako/Controller.php';
			
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

			// Setup class aliases

			class_alias('\mako\Mako', 'Mako');
			
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
			
			// Include bootstrap file
			
			require MAKO_APPLICATION . '/bootstrap.php';
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
			static $run;
			
			if(empty($run))
			{
				static::init();

				Request::factory($route)->execute();
				
				static::cleanup();

				Response::instance()->send();
				
				$run = true;
			}
		}
		
		/**
		* Returns true if we are running on Windows and false if not.
		*
		* @access  public
		* @return  boolean
		*/
		
		public static function isWindows()
		{
			return static::$isWindows;
		}
		
		/**
		* Will try to autoload the requested class.
		*
		* @access  public
		* @param   string  Class name
		*/
		
		public static function classLoader($className)
		{
			$className = ltrim($className, '\\');

			$fileName  = '';
			$namespace = '';

			if($lastNsPos = strripos($className, '\\'))
			{
				$namespace = substr($className, 0, $lastNsPos);
				$className = substr($className, $lastNsPos + 1);
				$fileName  = str_replace('\\', '/', $namespace) . '/';
			}

			$fileName .= str_replace('_', '/', $className) . '.php';

			// Try to load library class

			if(file_exists(MAKO_LIBRARIES_PATH . '/' . $fileName))
			{
				include(MAKO_LIBRARIES_PATH . '/' . $fileName);
				
				return true;
			}

			// Try to load application class

			$fileName = strtolower($fileName); // app class file names should be lowercase to maintain backwards compatibility

			if(file_exists(MAKO_APPLICATION_PATH . '/' . $fileName))
			{
				include(MAKO_APPLICATION_PATH . '/' . $fileName);
				
				return true;
			}

			// Nothing was found

			return false;
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
		
		public static function config($group)
		{
			if(isset(static::$config[$group]) === false)
			{
				$file = MAKO_APPLICATION . '/config/' . $group . '.php';
					
				if(file_exists($file) === false)
				{
					throw new RuntimeException(vsprintf("%s(): The '%s' config file does not exist.", array(__METHOD__, $group)));
				}	

				static::$config[$group] = new ArrayObject(include($file), ArrayObject::ARRAY_AS_PROPS);
					
				if($group !== 'cache')
				{
					static::$updateCache = true;
				}
			}
				
			return static::$config[$group];
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
						'number'      => str_pad($currentLine, 4, '0', STR_PAD_LEFT),
						'highlighted' => ($currentLine === $line),
						'code'        => str_replace("\t", '    ', htmlspecialchars($temp)),
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

					if(Request::isCli())
					{
						if(empty($error['file']))
						{
							fwrite(STDERR, "{$error['type']}: {$error['message']}" . PHP_EOL);
						}
						else
						{
							fwrite(STDERR, "{$error['type']}: {$error['message']} in {$error['file']} at line {$error['line']}" . PHP_EOL);
						}
					}
					else
					{
						include MAKO_APPLICATION . '/views/_errors/exception.php'; // Include file instead of using view class as it can cause problems with fatal errors.
					}
				}
				else
				{
					if(Request::isCli())
					{
						fwrite(STDERR, "An error has occured." . PHP_EOL);
					}
					else
					{
						include MAKO_APPLICATION . '/views/_errors/error.php'; // Include file instead of using view class as it can cause problems with fatal errors.
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
}

/** -------------------- End of file --------------------**/