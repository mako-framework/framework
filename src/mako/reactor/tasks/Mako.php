<?php

namespace mako\reactor\tasks;

use \mako\reactor\tasks\console\Console;

/**
 * Mako task.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Mako extends \mako\reactor\Task
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
		'console' => array
		(
			'description' => 'Starts a debug console.',
			'options'     => array
			(
				'fresh'  => 'Start without the console history.',
				'forget' => 'Discard the console history upon exit.',
			),
		),
		'server' => array
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
	 * Starts the console.
	 * 
	 * @access  public
	 */

	public function console()
	{
		$console = new Console($this->input, $this->output);

		$console->run();
	}

	/**
	 * Starts the server.
	 * 
	 * @access  public
	 */

	public function server()
	{
		// Check if PHP version requirement is met

		if(version_compare(PHP_VERSION, '5.4.0', '<'))
		{
			return $this->output->error('You need PHP 5.4.0 or greater to use the development server.');
		}

		// Start server

		$port    = $this->input->param('port', 8000);
		$address = $this->input->param('address', 'localhost');
		$docroot = $this->input->param('docroot', MAKO_APPLICATION_PARENT_PATH);

		$host = ($address === '0.0.0.0') ? gethostbyname(gethostname()) : $address;

		$message  = 'Starting <green>Mako</green> development server at ';
		$message .= '<options=underscore>http://' . $host . ':' . $port . '</options=underscore> ';
		$message .= '<yellow>(ctrl+c to stop)</yellow> ...';

		$this->output->writeln($message);

		passthru(PHP_BINDIR . '/php -S ' . $address . ':' . $port . ' -t ' . $docroot . ' ' . __DIR__ . '/server/router.php');
	}
}

/** -------------------- End of file -------------------- **/