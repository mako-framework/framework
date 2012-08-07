<?php

namespace mako\reactor\handlers;

use \mako\CLI;
use \mako\Rest;
use \mako\reactor\Reactor;

/**
* Package installer.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Packages
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	* API server.
	*
	* @var string
	*/

	const API = 'http://packages.makoframework.com/get/';

	/**
	* Install command for linux/unix.
	*
	* @var string
	*/

	const INSTALL_X = 'git clone --depth 1 %1$s %2$s/%3$s/ && rm -rf %2$s/%3$s/.git';

	/**
	* Install command for windows.
	*
	* @var string
	*/

	const INSTALL_W = 'git clone --depth 1 %1$s %2$s\%3$s\ && rmdir /s /q %2$s\%3$s\.git';

	/**
	* Remove command for linux/unix.
	*
	* @var string
	*/

	const REMOVE_X = 'rm -rfv %s/%s';

	/**
	* Remove command for windows.
	*
	* @var string
	*/

	const REMOVE_W = 'rmdir /s /q %s\%s';

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
	* Installs or uninstalls the requested package.
	*
	* @access  public
	* @param   array   $arguments  Arguments
	*/

	public static function run($arguments)
	{
		if(count($arguments) > 1)
		{
			switch($arguments[0])
			{
				case 'i':
				case 'install':
					foreach(array_slice($arguments, 1) as $package)
					{
						static::install($package);
					}
					
					return;
				break;
				case 'r':
				case 'remove':
					foreach(array_slice($arguments, 1) as $package)
					{
						static::remove($package);
					}
					
					return;
			}
		}
		
		Reactor::help();
	}

	/**
	* Installs a package.
	*
	* @access  protected
	* @param   string     $package  Package name
	* @param   boolean    $silent   (optional) Silent install?
	*/

	protected static function install($package, $silent = false)
	{
		$repo = true;

		if(strpos($package, '://') !== false)
		{
			$repo = false;

			$url = $package;

			preg_match('/^(.*):\/\/(.*)\/(.*).git$/i', $package, $matches);

			if(empty($matches))
			{
				return CLI::stderr('Invalid package URL.');
			}

			$name = $package = $matches[3];
		}

		if(is_dir(MAKO_PACKAGES . DIRECTORY_SEPARATOR . $package))
		{
			return ($silent) ? null : CLI::stderr('The ' . $package . ' package has already been installed.');
		}

		if($repo === true)
		{
			CLI::stdout('Fetching package info from packages.makoframework.com ...');

			$response = Rest::factory(static::API . $package)->get($info);

			if($info['http_code'] != 200)
			{
				return CLI::stderr('No response from server.');
			}

			$response = json_decode($response);

			if($response->status !== 'ok')
			{
				return CLI::stderr('The ' . $package . ' package does not exist.');
			}

			foreach($response->package->dependencies as $dep)
			{
					static::install($dep, true);
			}

			$url = sprintf('git://github.com/%s.git', $response->package->repo);

			$name = $response->package->name;
		}

		CLI::stdout(sprintf('Fetching package from %s ...', $url));

		passthru(sprintf(MAKO_IS_WINDOWS ? static::INSTALL_W : static::INSTALL_X, $url, MAKO_PACKAGES, $name));

		if($repo === true)
		{
			@file_put_contents(MAKO_PACKAGES . DIRECTORY_SEPARATOR . $package . DIRECTORY_SEPARATOR . 'package.json', json_encode($response->package));
		}

		CLI::stdout('The '. $package . ' package has been installed!');
	}

	/**
	* Removes a package.
	*
	* @access  public
	* @param   string  $package  Package name
	*/

	protected static function remove($package)
	{
		if(!is_dir(MAKO_PACKAGES . DIRECTORY_SEPARATOR . $package))
		{
			return CLI::stderr('The ' . $package . ' package is not installed.');
		}

		CLI::stdout('Deleting ' . $package . ' files ...');

		passthru(sprintf(MAKO_IS_WINDOWS ? static::REMOVE_W : static::REMOVE_X, MAKO_PACKAGES, $package));

		CLI::stdout('The ' . $package . ' package has been removed.');
	}
}

/** -------------------- End of file --------------------**/