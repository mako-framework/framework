<?php

namespace mako\reactor\tasks;

use \mako\Config;
use \mako\reactor\CLI;
use \mako\Package as Pckg;

/**
 * Package task.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Package extends \mako\reactor\Task
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Lists the available actions.
	 * 
	 * @access  public
	 */

	public function run()
	{
		return $this->listActions();
	}

	/**
	 * Links package assets to the app.
	 * 
	 * @access  public
	 */

	public function link_assets($package)
	{
		$assetsDir = $this->cli->param('assets-dir', dirname(MAKO_APPLICATION_PARENT_PATH) . Config::get('application.asset_location'));

		if(!Pckg::installed($package))
		{
			return $this->cli->stderr(vsprintf("The '%s' package does not appear to be installed.", array($package)));
		}
		elseif(!is_dir(MAKO_PACKAGES_PATH . '/' . $package . '/assets'))
		{
			return $this->cli->stderr(vsprintf("The '%s' package does not appear to have any assets.", array($package)));
		}
		elseif(!is_dir($assetsDir))
		{
			return $this->cli->stderr(vsprintf("The assets directory ('%s') does not exist.", array($package)));
		}
		elseif(!is_writable($assetsDir))
		{
			return $this->cli->stderr("Insufficient permissions to link the assets.");
		}
		elseif(is_link($assetsDir . '/' . $package) || is_dir($assetsDir . '/' . $package))
		{
			return $this->cli->stderr(vsprintf("The '%s' package assets has already been linked.", array($package)));
		}
		else
		{
			symlink(MAKO_PACKAGES_PATH . '/' . $package . '/assets', $assetsDir . '/' . $package);

			$this->cli->stdout('Link created.');
		}
	}

	/**
	 * Removes the package assets link from the app.
	 * 
	 * @access  public
	 */

	public function unlink_assets($package)
	{
		$assetsDir = $this->cli->param('assets-dir', dirname(MAKO_APPLICATION_PARENT_PATH) . Config::get('application.asset_location'));

		if(!is_dir($assetsDir))
		{
			return $this->cli->stderr(vsprintf("The assets directory ('%s') does not exist.", array($package)));
		}
		elseif(!is_link($assetsDir . '/' . $package))
		{
			return $this->cli->stderr(vsprintf("The '%s' package assets isn't linked.", array($package)));
		}
		elseif(!is_writable($assetsDir . '/' . $package))
		{
			return $this->cli->stderr("Insufficient permissions to unlink the assets.");
		}
		else
		{
			unlink($assetsDir . '/' . $package);

			$this->cli->stdout('Link removed.');
		}
	}
}

/** -------------------- End of file --------------------**/