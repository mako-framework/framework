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
	* Default method that displays basic help.
	*
	* @access  public
	*/

	public function run()
	{
		
		CLI::stdout('Mako package installer:', null, null, array('bold'));		
		CLI::stdout();
		CLI::stdout('* php reactor package.search "<search terms>"');
		CLI::stdout('* php reactor package.install <package name> or <repository url>');
		CLI::stdout('* php reactor package.remove <package name>');
	}

	/**
	* Search the Mako package repository and display results.
	*
	* @access  public
	* @param   string  $package  Package name
	*/

	public function search($package)
	{
		CLI::stderr('Package search has not been implemented yet.');
	}

	/**
	* Installs package from the Mako repository.
	*
	* @access  protected
	* @param   string     $package  Package name
	* @param   string     $silent   (optional) Silent mode?
	* @return  boolean
	*/

	protected function repository($package, $silent = false)
	{
		if(is_dir(MAKO_PACKAGES . DIRECTORY_SEPARATOR . $package))
		{
			!$silent && CLI::stderr('The ' . $package . ' package has already been installed.');

			return false;
		}

		CLI::stdout('Fetching package info from packages.makoframework.com ...');

		$response = Rest::factory(static::API . $package)->get($info);

		if($info['http_code'] != 200)
		{
			CLI::stderr('No response from server.');

			return false;
		}

		$response = json_decode($response);

		if($response->status !== 'ok')
		{
			CLI::stderr('The ' . $package . ' package does not exist.');

			return false;
		}

		foreach($response->package->dependencies as $dep)
		{
			$this->repository($dep, true);
		}

		$url = sprintf('git://github.com/%s.git', $response->package->repo);

		$name = $response->package->name;

		CLI::stdout(sprintf('Fetching package from %s ...', $url));

		passthru(sprintf(MAKO_IS_WINDOWS ? static::INSTALL_W : static::INSTALL_X, $url, MAKO_PACKAGES, $name));

		file_put_contents(MAKO_PACKAGES . DIRECTORY_SEPARATOR . $package . DIRECTORY_SEPARATOR . 'package.json', json_encode($response->package));

		return true;
	}

	/**
	* Installs package from a URL.
	*
	* @access  protected
	* @param   string     $package  Package URL
	* @return  boolean
	*/

	protected function url($package)
	{
		$url = $package;

		preg_match('/^(.*):\/\/(.*)\/(.*).git$/i', $package, $matches);

		if(empty($matches))
		{
			CLI::stderr('Invalid package URL.');

			return false;
		}

		$package = $matches[3];

		if(is_dir(MAKO_PACKAGES . DIRECTORY_SEPARATOR . $package))
		{
			!$silent && CLI::stderr('The ' . $package . ' package has already been installed.');

			return false;
		}

		CLI::stdout(sprintf('Fetching package from %s ...', $url));

		passthru(sprintf(MAKO_IS_WINDOWS ? static::INSTALL_W : static::INSTALL_X, $url, MAKO_PACKAGES, $package));

		return true;
	}

	/**
	* Installs a package.
	*
	* @access  public
	* @param   string  $package  Package name or URL
	*/

	public function install($package)
	{
		if(!is_writable(MAKO_PACKAGES))
		{
			return CLI::stderr('The package directory isn\'t writable.');
		}

		if(strpos($package, '://') !== false)
		{
			$success = $this->url($package);
		}
		else
		{
			$success = $this->repository($package);
		}

		if($success)
		{
			CLI::stdout('The package has successfully been installed!');
		}
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