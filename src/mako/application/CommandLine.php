<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application;

use \mako\application\Application;
use \mako\reactor\Reactor;
use \mako\reactor\TaskFinder;
use \mako\reactor\io\Input;
use \mako\reactor\io\Output;

/**
 * Web application.
 *
 * @author  Frederic G. Østby
 */

class CommandLine extends Application
{
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
	 * {@inheritdoc}
	 */

	public function run()
	{
		$output = new Output();

		$input = new Input($output);

		$tasks = (new TaskFinder($this))->find();

		(new Reactor($input, $output, $this->container, $tasks))->run();
	}
}