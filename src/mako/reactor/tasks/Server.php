<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\reactor\tasks;

use \mako\application\Application;
use \mako\reactor\io\Input;
use \mako\reactor\io\Output;

/**
 * Server task.
 *
 * @author  Frederic G. Østby
 */

class Server extends \mako\reactor\Task
{
	/**
	 * Application instance.
	 * 
	 * @var \mako\application\Application
	 */

	protected $application;

	/**
	 * Task information.
	 * 
	 * @var array
	 */

	protected static $taskInfo = 
	[
		'start' => 
		[
			'description' => 'Starts the local development server.',
			'options'     => 
			[
				'port'    => 'Port to run the server on.',
				'address' => 'Address to run the server on.',
				'docroot' => 'Path to the document root.',
			],
		],
	];

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\reactor\io\Input         $input        Input
	 * @param   \mako\reactor\io\Output        $output       Output
	 * @param   \mako\application\Application  $application  Application instance
	 */

	public function __construct(Input $input, Output $output, Application $application)
	{
		parent::__construct($input, $output);

		$this->application = $application;
	}

	/**
	 * Starts the server.
	 * 
	 * @access  public
	 */

	public function start()
	{
		// Check if PHP version requirement is met

		if(version_compare(PHP_VERSION, '5.4.0', '<'))
		{
			return $this->output->error('You need PHP 5.4.0 or greater to use the development server.');
		}

		// Start server

		$port    = $this->input->param('port', 8000);
		$address = $this->input->param('address', 'localhost');
		$docroot = $this->input->param('docroot', dirname($this->application->getPath()));

		$host = ($address === '0.0.0.0') ? gethostbyname(gethostname()) : $address;

		$message  = 'Starting <green>Mako</green> development server at ';
		$message .= '<options=underscore>http://' . $host . ':' . $port . '</options=underscore> ';
		$message .= '<yellow>(ctrl+c to stop)</yellow> ...';

		$this->output->writeln($message);

		passthru(PHP_BINDIR . '/php -S ' . $address . ':' . $port . ' -t ' . $docroot . ' ' . __DIR__ . '/server/router.php');
	}
}