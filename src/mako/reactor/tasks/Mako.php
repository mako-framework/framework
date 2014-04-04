<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\reactor\tasks;

use \mako\core\Application;
use \mako\reactor\io\Input;
use \mako\reactor\io\Output;
use \mako\reactor\tasks\console\Boris;
use \mako\reactor\tasks\console\Console;

/**
 * Mako task.
 *
 * @author  Frederic G. Østby
 */

class Mako extends \mako\reactor\Task
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Application instance.
	 * 
	 * @var \mako\core\Application
	 */

	protected $application;

	/**
	 * Task information.
	 * 
	 * @var array
	 */

	protected static $taskInfo = 
	[
		'console' => 
		[
			'description' => 'Starts the debug console.',
			'options'     => 
			[
				'fresh'  => 'Start without the console history.',
				'forget' => 'Discard the console history upon exit.',
			],
		],
		'server' => 
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

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\reactor\io\Input   $input        Input
	 * @param   \mako\reactor\io\Output  $output       Output
	 * @param   \mako\core\Application   $application  Application instance
	 */

	public function __construct(Input $input, Output $output, Application $application)
	{
		parent::__construct($input, $output);

		$this->application = $application;
	}

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
		// Check if any of the pcntl functions are disabled

		$disabled = false;

		$disabledFunctions = explode(',', ini_get('disable_functions'));

		foreach($disabledFunctions as $function)
		{
			if(strpos($function, 'pcntl') !== false)
			{
				$disabled = true;

				break;
			}
		}

		// Clear screen

		$this->output->clearScreen();

		// Define path to history file

		$history = $this->application->getApplicationPath() . '/storage/console_history';

		// Start Boris if all the requirements are met and fall back to the default console if not

		if(extension_loaded('readline') && extension_loaded('pcntl') && extension_loaded('posix') && !$disabled)
		{
			// Start Boris REPL

			$console = new Boris($this->input, $this->output, $history);

			$console->run();
		}
		else
		{
			// Start fallback REPL

			$console = new Console($this->input, $this->output, $history);

			$console->run();
		}
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
		$docroot = $this->input->param('docroot', dirname($this->application->getApplicationPath()));

		$host = ($address === '0.0.0.0') ? gethostbyname(gethostname()) : $address;

		$message  = 'Starting <green>Mako</green> development server at ';
		$message .= '<options=underscore>http://' . $host . ':' . $port . '</options=underscore> ';
		$message .= '<yellow>(ctrl+c to stop)</yellow> ...';

		$this->output->writeln($message);

		passthru(PHP_BINDIR . '/php -S ' . $address . ':' . $port . ' -t ' . $docroot . ' ' . __DIR__ . '/server/router.php');
	}
}