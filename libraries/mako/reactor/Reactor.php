<?php

namespace mako\reactor;

use \mako\Mako;
use \mako\Config;
use \mako\CLI;
use \mako\reactor\handlers\Tasks;
use \mako\reactor\handlers\Packages;

/**
* Reactor core class.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Reactor
{
	//---------------------------------------------
	// Class variables
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
	* Sets up the CLI environment and runs the commands.
	*
	* @access  public
	* @param   array   Arguments
	*/

	public static function run($arguments)
	{
		// Set internal encoding

		mb_language('uni');
		mb_regex_encoding(MAKO_CHARSET);
		mb_internal_encoding(MAKO_CHARSET);

		// Set timezone and locale settings

		$config = Config::get('mako');

		date_default_timezone_set($config['timezone']);

		Mako::locale($config['locale']['locales'], $config['locale']['lc_numeric']);

		// Remove options from argument list so that it doesnt matter what order they come in

		foreach($arguments as $key => $value)
		{
			if(substr($value, 0, 2) == '--')
			{
				unset($arguments[$key]);
			}
		}

		// Handle commands

		CLI::stdout();

		if(!empty($arguments))
		{
			switch($arguments[0])
			{
				case 'v':
				case 'version':
					return CLI::stdout('Mako '. Mako::VERSION . ' (PHP ' . phpversion() . ' | Zend Engine ' . zend_version() . ' | ' . PHP_OS . ')');
				break;
				case 'p':
				case 'package':
					return Packages::run(array_slice($arguments, 1));
				break;
				case 't':
				case 'task':
					return Tasks::run(array_slice($arguments, 1));
			}
		}
		
		static::help();
	}

	/**
	* Displays help.
	*
	* @access  public
	*/

	public static function help()
	{
		$help  = 'Reactor CLI tool' . PHP_EOL;
		$help .= '-------------------------------------------------------' . PHP_EOL . PHP_EOL;
		$help .= 'Valid commands are:'  . PHP_EOL . PHP_EOL;
		$help .= '   php reactor version'  . PHP_EOL;
		$help .= '   php reactor task <taskname>'  . PHP_EOL;
		$help .= '   php reactor package install <package name>' . PHP_EOL;
		$help .= '   php reactor package remove <package name>' . PHP_EOL . PHP_EOL;
		$help .= 'Mako framework documentation at http://makoframework.com/docs';

		CLI::stdout($help);
	}
}

/** -------------------- End of file --------------------**/