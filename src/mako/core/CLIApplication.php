<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\core;

use \mako\reactor\Reactor;
use \mako\reactor\TaskFinder;
use \mako\reactor\io\Input;
use \mako\reactor\io\Output;

/**
 * Web application.
 *
 * @author  Frederic G. Østby
 */

class CLIApplication extends \mako\core\Application
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

		$tasks = (new TaskFinder($this->getApplicationPath()))->find();

		(new Reactor($input, $output, $this->container, $tasks))->run();
	}
}