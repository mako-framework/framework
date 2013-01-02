<?php

namespace mako\reactor\tasks;

use \mako\reactor\CLI;

/**
 * Development server.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Server extends \mako\reactor\Task
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
	 * Starts the server.
	 * 
	 * @access  public
	 */

	public function run()
	{
		// Check if PHP version requirement is met

		if(version_compare(PHP_VERSION, '5.4.0', '<'))
		{
			return $this->cli->stderr('PHP 5.4.0 or greater is required.');
		}

		// Start server

		$port    = $this->cli->param('port', 8000);
		$address = $this->cli->param('address', 'localhost');
		$docroot = $this->cli->param('docroot', MAKO_APPLICATION_PARENT_PATH);

		$this->cli->stdout('Starting ' . $this->cli->color('Mako', 'green') . ' development server at ' . $this->cli->style('http://' . $address . ':' . $port, array('underlined')) . ' ' . $this->cli->color('(CTRL+C to stop)', 'yellow') . ' ...');

		passthru('php -S ' . $address . ':' . $port . ' -t ' . $docroot . ' ' . __DIR__ . '/server/router.php');
	}
}

/** -------------------- End of file --------------------**/