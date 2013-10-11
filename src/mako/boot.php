<?php

//------------------------------------------------------------------------------------------
// Define some constants
//------------------------------------------------------------------------------------------

define('MAKO_VERSION', '4.0.0');
define('MAKO_START', microtime(true));
define('MAKO_MAGIC_QUOTES', get_magic_quotes_gpc());
define('MAKO_IS_WINDOWS', (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'));
define('MAKO_APPLICATION_PARENT_PATH', dirname(MAKO_APPLICATION_PATH));
define('MAKO_APPLICATION_ID', md5(MAKO_APPLICATION_PATH));
define('MAKO_PACKAGES_PATH', MAKO_APPLICATION_PATH . '/packages');

//------------------------------------------------------------------------------------------
// Set up autoloading
//------------------------------------------------------------------------------------------

include  __DIR__ . '/core/ClassLoader.php';

mako\core\ClassLoader::register();

//------------------------------------------------------------------------------------------
// Set up error handling
//------------------------------------------------------------------------------------------

// Convert all errors to ErrorExceptions

set_error_handler(function($code, $message, $file, $line)
{
	if((error_reporting() & $code) !== 0)
	{
		throw new ErrorException($message, $code, 0, $file, $line);
	}

	return true;
});

// Set default path for error logs

ini_set('error_log', MAKO_APPLICATION_PATH . '/storage/logs/error_' . gmdate('Y_m_d') . '.log');

// Register error handler

mako\core\errorhandler\ErrorHandler::register();

//------------------------------------------------------------------------------------------
// Define helper functions
//------------------------------------------------------------------------------------------

/**
 * Returns path to a package or application directory.
 *
 * @param   string  $path  Path
 * @param   string  $file  File
 * @param   string  $ext   (optional) File extension
 * @return  string
 */

function mako_path($path, $file, $ext = '.php')
{
	if(strpos($file, '::') !== false)
	{
		list($package, $file) = explode('::', $file);

		$path = MAKO_PACKAGES_PATH . '/' . $package . '/' . $path . '/' . $file . $ext;
	}
	else
	{
		$path = MAKO_APPLICATION_PATH . '/' . $path . '/' . $file . $ext;
	}

	return $path;
}

/**
 * Returns an array of cascading paths to a package or application directory.
 *
 * @param   string  $path  Path
 * @param   string  $file  String
 * @param   string  $ext   (optional) File extension
 * @return  array
 */

function mako_cascading_paths($path, $file, $ext = '.php')
{
	$paths = array();

	if(strpos($file, '::') !== false)
	{
		list($package, $file) = explode('::', $file);

		$paths[] = MAKO_APPLICATION_PATH . '/' . $path . '/packages/' . $package . '/' . $file . $ext;

		$paths[] = MAKO_PACKAGES_PATH . '/' . $package . '/' . $path . '/' . $file . $ext;
	}
	else
	{
		$paths[] = MAKO_APPLICATION_PATH . '/' . $path . '/' . $file . $ext;
	}

	return $paths;
}

/**
 * Returns the Mako environment. NULL is returned if no environment is specified.
 * 
 * @return  mixed
 */

function mako_env()
{
	return getenv('MAKO_ENV') ?: null;
}

if(!function_exists('__'))
{
	/**
	 * Alias of mako\I18n::get()
	 *
	 * Returns a translated string of the current language. 
	 * If no translation exists then the submitted string will be returned.
	 *
	 * @param   string  Text to translate
	 * @param   array   (optional) Value or array of values to replace in the translated text
	 * @param   string  (optional) Name of the language you want to translate to
	 * @return  string
	 */

	function __($string, array $vars = array(), $language = null)
	{
		return mako\I18n::get($string, $vars, $language);
	}
}

if(!function_exists('dump_var'))
{
	/**
	 * Works like var_dump except that it wraps the variable in <pre> tags.
	 *
	 * @param   mixed   Variable you want to dump
	 */

	function dump_var()
	{
		ob_start();

		call_user_func_array('var_dump', func_get_args());

		echo '<pre>' . ob_get_clean() . '</pre>';
	}
}

if(!function_exists('debug'))
{
	/**
	 * Add entry to the debug toolbar log.
	 *
	 * @param   mixed   $log   Item you want to log
	 * @param   string  $type  Log type
	 * @return  mixed
	 */

	function debug($log, $type = mako\core\DebugToolbar::DEBUG)
	{
		return mako\core\DebugToolbar::log($log, $type, true);
	}
}

//------------------------------------------------------------------------------------------
// Configure the core
//------------------------------------------------------------------------------------------

$config = mako\core\Config::get('application');

// Set internal charset

define('MAKO_CHARSET', $config['charset']);

mb_language('uni');
mb_regex_encoding(MAKO_CHARSET);
mb_internal_encoding(MAKO_CHARSET);

// Set default timezone

date_default_timezone_set($config['timezone']);

// Set locale information

setlocale(LC_ALL, $config['locale']['locales']);
	
if($config['locale']['lc_numeric'] === false)
{
	setlocale(LC_NUMERIC, 'C');
}

// Set up class aliases

foreach($config['aliases'] as $alias => $className)
{
	mako\core\ClassLoader::alias($alias, $className);
}

// Initialize packages

foreach($config['packages'] as $package)
{
	mako\core\Package::init($package);
}

unset($config);

//------------------------------------------------------------------------------------------
// Include application bootstrap file
//------------------------------------------------------------------------------------------

if(file_exists(MAKO_APPLICATION_PATH . '/bootstrap.php'))
{
	include MAKO_APPLICATION_PATH . '/bootstrap.php';
}

/** -------------------- End of file -------------------- **/