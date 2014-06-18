<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application;

use \mako\error\handlers\CLIHandler;
use \mako\reactor\Reactor;
use \mako\reactor\TaskFinder;
use \mako\reactor\io\Input;
use \mako\reactor\io\Output;

/**
 * Web application.
 *
 * @author  Frederic G. Østby
 */

class CommandLine extends \mako\application\Application
{
	/**
	 * Register the error handler.
	 * 
	 * @access  protected
	 */

	protected function registerErrorHandler()
	{
		$this->container->get('errorHandler')->handle('\Exception', function($exception)
		{
			// Create handler instance

			$handler = new CLIHandler($exception);

			// Set logger if error logging is enabled

			if($this->config->get('application.error_handler.log_errors'))
			{
				$handler->setLogger($this->container->get('logger'));
			}

			// Handle the error
			
			return $handler->handle($this->config->get('application.error_handler.display_errors'));
		});
	}

	/**
	 * Returns the route collection.
	 * 
	 * @access  public
	 * @return  \mako\http\routing\Routes
	 */

	public function getRouteCollection()
	{
		return $this->loadRoutes();
	}

	/**
	 * Runs the application.
	 * 
	 * @access  public
	 */

	public function run()
	{
		$output = new Output();

		$input = new Input($output);

		$tasks = (new TaskFinder($this))->find();

		(new Reactor($input, $output, $this->container, $tasks))->run();
	}
}