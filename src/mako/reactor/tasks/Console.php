<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\reactor\tasks;

use \mako\application\Application;
use \mako\reactor\io\Input;
use \mako\reactor\io\Output;
use \mako\reactor\tasks\console\Boris;
use \mako\reactor\tasks\console\REPL;

/**
 * Console task.
 *
 * @author  Frederic G. Østby
 */

class Console extends \mako\reactor\Task
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
			'description' => 'Starts the debug console.',
			'options'     => 
			[
				'fresh'  => 'Start without the console history.',
				'forget' => 'Discard the console history upon exit.',
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
	 * Starts the console.
	 * 
	 * @access  public
	 */

	public function start()
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
			// Disable mako error handlers

			restore_error_handler();
			
			restore_exception_handler();

			$this->application->getContainer()->get('errorHandler')->disableShutdownHandler();

			// Start Boris REPL

			$console = new Boris($this->input, $this->output, $history);

			$console->run();
		}
		else
		{
			// Start fallback REPL

			$console = new REPL($this->input, $this->output, $history);

			$console->run();
		}
	}
}