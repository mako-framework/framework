<?php

namespace mako\reactor
{
	use \mako\Mako;
	use \mako\CLI;
	use \mako\reactor\Tasks;
	use \mako\reactor\Bundles;

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
			mb_regex_encoding('UTF-8');
			mb_internal_encoding('UTF-8');

			// Set timezone and locale settings

			$config = Mako::config('mako');

			date_default_timezone_set($config['timezone']);

			Mako::locale($config['locale']['locales'], $config['locale']['lc_numeric']);

			// Remove options from argument list

			$arguments = static::stripOptions($arguments);

			// Handle input

			CLI::stdout();

			if(!empty($arguments))
			{
				switch($arguments[0])
				{
					case 'v':
					case 'version':
						return CLI::stdout('Mako v'. Mako::VERSION . ' (PHP v' . phpversion() . ' | ' . PHP_OS . ')');
					break;
					case 'b':
					case 'bundle':
						return Bundles::run(array_slice($arguments, 1));
					break;
					case 't':
					case 'task':
						return Tasks::run(array_slice($arguments, 1));
				}
			}
			
			static::help();
		}

		/**
		* Strips options from the argument list.
		*
		* @access  protected
		* @param   array      Array to clean
		* @return  array
		*/

		protected static function stripOptions($arguments)
		{
			foreach($arguments as $key => $value)
			{
				if(substr($value, 0, 2) == '--')
				{
					unset($arguments[$key]);
				}
			}

			return $arguments;
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
			$help .= '   php reactor bundle install <bundlename>' . PHP_EOL;
			$help .= '   php reactor bundle remove <bundlename>' . PHP_EOL . PHP_EOL;
			$help .= 'Mako framework documentation at http://makoframework.com/docs';

			CLI::stdout($help);
		}
	}
}

/** -------------------- End of file --------------------**/