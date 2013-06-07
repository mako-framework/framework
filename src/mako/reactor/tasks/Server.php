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

	/**
	 * Task information.
	 * 
	 * @var array
	 */

	protected static $taskInfo = array
	(
		'run' => array
		(
			'description' => 'Starts a local development server.',
			'options'     => array
			(
				'port'    => 'Port to run the server on.',
				'address' => 'Address to run the server on.',
				'docroot' => 'Path to the document root.',
			),
		),
	);

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
			return $this->cli->stderr('You need PHP 5.4.0 or greater to use the development server.');
		}

		// Start server

		$port    = $this->cli->param('port', 8000);
		$address = $this->cli->param('address', 'localhost');
		$docroot = $this->cli->param('docroot', MAKO_APPLICATION_PARENT_PATH);

		$host = ($address === '0.0.0.0') ? gethostbyname(gethostname()) : $address;

		$message  = 'Starting ' . $this->cli->color('Mako', 'green') . ' development server at ';
		$message .= $this->cli->style('http://' . $host . ':' . $port, array('underlined')) . ' ';
		$message .= $this->cli->color('(ctrl+c to stop)', 'yellow') . ' ...' . PHP_EOL;

		$this->cli->stdout($message);

		passthru(PHP_BINDIR . '/php -S ' . $address . ':' . $port . ' -t ' . $docroot . ' ' . __DIR__ . '/server/router.php');
	}
}

/** -------------------- End of file -------------------- **/