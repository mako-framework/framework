<?php

namespace mako
{
	use \mako\Log;
	use \mako\UTF8;
	use \mako\View;
	use \mako\Cache;
	use \mako\ClassLoader;
	use \mako\Request;
	use \mako\Response;
	use \Exception;
	use \ErrorException;
	use \RuntimeException;
	use \ArrayObject;

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
		
		const VERSION = '1.5.1';
		
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
		
		public static function init()
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
			define('MAKO_BUNDLES', MAKO_APPLICATION . '/bundles');
			
			// Map all core classes

			require MAKO_LIBRARIES_PATH . '/mako/ClassLoader.php';

			ClassLoader::addClasses(array
			(
				'mako\ArrayTo'           => MAKO_LIBRARIES_PATH . '/mako/ArrayTo.php',
				'mako\Benchmark'         => MAKO_LIBRARIES_PATH . '/mako/Benchmark.php',
				'mako\CLI'               => MAKO_LIBRARIES_PATH . '/mako/CLI.php',
				'mako\Cache'             => MAKO_LIBRARIES_PATH . '/mako/Cache.php',
				'mako\cache\APC'         => MAKO_LIBRARIES_PATH . '/mako/cache/APC.php',
				'mako\cache\Adapter'     => MAKO_LIBRARIES_PATH . '/mako/cache/Adapter.php',
				'mako\cache\File'        => MAKO_LIBRARIES_PATH . '/mako/cache/File.php',
				'mako\cache\Memcache'    => MAKO_LIBRARIES_PATH . '/mako/cache/Memcache.php',
				'mako\cache\Memcached'   => MAKO_LIBRARIES_PATH . '/mako/cache/Memcached.php',
				'mako\cache\Memory'      => MAKO_LIBRARIES_PATH . '/mako/cache/Memory.php',
				'mako\cache\Redis'       => MAKO_LIBRARIES_PATH . '/mako/cache/Redis.php',
				'mako\cache\SQLite'      => MAKO_LIBRARIES_PATH . '/mako/cache/SQLite.php',
				'mako\cache\WinCache'    => MAKO_LIBRARIES_PATH . '/mako/cache/WinCache.php',
				'mako\cache\XCache'      => MAKO_LIBRARIES_PATH . '/mako/cache/XCache.php',
				'mako\cache\ZendDisk'    => MAKO_LIBRARIES_PATH . '/mako/cache/ZendDisk.php',
				//'mako\ClassLoader'       => MAKO_LIBRARIES_PATH . '/mako/ClassLoader.php',
				'mako\cache\ZendMemory'  => MAKO_LIBRARIES_PATH . '/mako/ZendMemory.php',
				'mako\Controller'        => MAKO_LIBRARIES_PATH . '/mako/Controller.php',
				'mako\Cookie'            => MAKO_LIBRARIES_PATH . '/mako/Cookie.php',
				'mako\Crypto'            => MAKO_LIBRARIES_PATH . '/mako/Crypto.php',
				'mako\crypto\Adapter'    => MAKO_LIBRARIES_PATH . '/mako/crypto/Adapter.php',
				'mako\crypto\Mcrypt'     => MAKO_LIBRARIES_PATH . '/mako/crypto/Mcrypt.php',
				'mako\crypto\OpenSSL'    => MAKO_LIBRARIES_PATH . '/mako/crypto/OpenSSL.php',
				'mako\Curl'              => MAKO_LIBRARIES_PATH . '/mako/Curl.php',
				'mako\Database'          => MAKO_LIBRARIES_PATH . '/mako/Database.php',
				'mako\DateTime'          => MAKO_LIBRARIES_PATH . '/mako/DateTime.php',
				//'mako\Gravatar'          => MAKO_LIBRARIES_PATH . '/mako/File.php',
				'mako\Growl'             => MAKO_LIBRARIES_PATH . '/mako/Gravatar.php',
				'mako\I18n'              => MAKO_LIBRARIES_PATH . '/mako/I18n.php',
				'mako\Image'             => MAKO_LIBRARIES_PATH . '/mako/Image.php',
				'mako\image\Adapter'     => MAKO_LIBRARIES_PATH . '/mako/image/Adapter.php',
				'mako\image\GD'          => MAKO_LIBRARIES_PATH . '/mako/image/GD.php',
				'mako\image\ImageMagick' => MAKO_LIBRARIES_PATH . '/mako/image/ImageMagick.php',
				'mako\image\Imagick'     => MAKO_LIBRARIES_PATH . '/mako/image/Imagick.php',
				'mako\Input'             => MAKO_LIBRARIES_PATH . '/mako/Input.php',
				'mako\Log'               => MAKO_LIBRARIES_PATH . '/mako/Log.php',
				'mako\log\Adapter'       => MAKO_LIBRARIES_PATH . '/mako/log/Adapter.php',
				'mako\log\File'          => MAKO_LIBRARIES_PATH . '/mako/log/File.php',
				'mako\log\FirePHP'       => MAKO_LIBRARIES_PATH . '/mako/log/FirePHP.php',
				'mako\log\Growl'         => MAKO_LIBRARIES_PATH . '/mako/log/Growl.php',
				'mako\log\Prowl'         => MAKO_LIBRARIES_PATH . '/mako/log/Prowl.php',
				'mako\log\Syslog'        => MAKO_LIBRARIES_PATH . '/mako/log/Syslog.php',
				//'mako\Mako'              => MAKO_LIBRARIES_PATH . '/mako/Mako.php',
				'mako\Model'             => MAKO_LIBRARIES_PATH . '/mako/Model.php',
				'mako\Notification'      => MAKO_LIBRARIES_PATH . '/mako/Notification.php',
				'mako\Num'               => MAKO_LIBRARIES_PATH . '/mako/Num.php',
				'mako\Pagination'        => MAKO_LIBRARIES_PATH . '/mako/Pagination.php',
				'mako\Prowl'             => MAKO_LIBRARIES_PATH . '/mako/Prowl.php',
				//'mako\ReCaptcha'         => MAKO_LIBRARIES_PATH . '/mako/ReCaptcha.php',
				'mako\Redis'             => MAKO_LIBRARIES_PATH . '/mako/Redis.php',
				'mako\Request'           => MAKO_LIBRARIES_PATH . '/mako/Request.php',
				'mako\Response'          => MAKO_LIBRARIES_PATH . '/mako/Response.php',
				'mako\Security'          => MAKO_LIBRARIES_PATH . '/mako/Security.php',
				'mako\Session'           => MAKO_LIBRARIES_PATH . '/mako/Session.php',
				'mako\session\Adapter'   => MAKO_LIBRARIES_PATH . '/mako/session/Adapter.php',
				'mako\session\Database'  => MAKO_LIBRARIES_PATH . '/mako/session/Database.php',
				'mako\session\Redis'     => MAKO_LIBRARIES_PATH . '/mako/session/Redis.php',
				'mako\String'            => MAKO_LIBRARIES_PATH . '/mako/String.php',
				'mako\UTF8'              => MAKO_LIBRARIES_PATH . '/mako/UTF8.php',
				'mako\UUID'              => MAKO_LIBRARIES_PATH . '/mako/UUID.php',
				'mako\UserAgent'         => MAKO_LIBRARIES_PATH . '/mako/UserAgent.php',
				'mako\View'              => MAKO_LIBRARIES_PATH . '/mako/View.php',
			));

			// Set up autoloader
			
			spl_autoload_register('\mako\ClassLoader::autoLoad');

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

			// Setup class alias to maintain backwards compability

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
			$file = MAKO_BUNDLES . '/' . $bundle . '/init.php';

			if(file_exists($file))
			{
				return include $file;
			}

			throw new RuntimeException(vsprintf("%s(): Unable to initialize the '%s' bundle. Make sure that it has been installed.", array(__METHOD__, $bundle)));
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