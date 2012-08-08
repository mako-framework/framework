<?php

namespace mako\reactor\tasks;

use \mako\CLI;
use \mako\Rest;

/**
* Package installer.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Package extends \mako\reactor\Task
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	* Package repository API.
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
	// Class methods
	//---------------------------------------------

	/**
	*
	*/

	public function run()
	{
		
		CLI::stdout('Mako package installer:', null, null, array('bold'));		
		CLI::stdout();
		CLI::stdout('* php reactor package.search  <package name>');
		CLI::stdout('* php reactor package.install <package name> or <repository url>');
		CLI::stdout('* php reactor package.remove  <package name>');
	}

	/**
	*
	*/

	public function search()
	{
		CLI::stderr('Package search has not been implemented yet.');
	}

	/**
	*
	*/

	public function install()
	{

	}

	/**
	* Removes a package.
	*
	* @access  public
	* @param   string  $package  Package name
	*/

	public function remove($package)
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