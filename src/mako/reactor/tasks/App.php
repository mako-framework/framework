<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\reactor\tasks;

use \Closure;

use \mako\core\Application;
use \mako\reactor\io\Input;
use \mako\reactor\io\Output;
use \mako\utility\Str;

/**
 * App task.
 *
 * @author  Frederic G. Østby
 */

class App extends \mako\reactor\Task
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
		'generate_secret' => 
		[
			'description' => 'Generates a new application secret.',
		],
		'routes' => 
		[
			'description' => 'Lists the registered routes of the application.',
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
	 * Generates a new application secret.
	 * 
	 * @access  public
	 */

	public function generate_secret()
	{
		$configFile = $this->application->getApplicationPath() . '/config/application.php';

		if(!is_writable($configFile))
		{
			return $this->output->error('Unable to generate a new secret. Make sure that the "app/config/application.php" file is writable.');
		}

		$secret = str_replace(['"', '\''], ['|', '/'], Str::random(Str::ALNUM . Str::SYMBOLS, 32));

		$contents = file_get_contents($configFile);

		$contents = preg_replace('/\'secret\'(\s*)=>(\s*)\'(.*)\',/', '\'secret\'$1=>$2\'' . $secret . '\',', $contents);

		file_put_contents($configFile, $contents);

		$this->output->writeln('A new secret has been generated.');
	}

	/**
	 * Lists the registered routes of the application.
	 * 
	 * @access  public
	 */

	public function routes()
	{
		foreach($this->application->getRouteCollection()->getRoutes() as $route)
		{
			// Normalize action name

			$action = ($route->getAction() instanceof Closure) ? 'Closure' : $route->getAction();

			// Normalize before filter names

			$beforeFilters = [];

			foreach($route->getBeforeFilters() as $filter)
			{
				$beforeFilters[] = ($filter instanceof Closure) ? 'Closure' : $filter;
			}

			// Normalize after filter names

			$afterFilters = [];

			foreach($route->getAfterFilters() as $filter)
			{
				$beforeFilters[] = ($filter instanceof Closure) ? 'Closure' : $filter;
			}

			// Build table row

			$routes[] =  
			[
				$route->getRoute(),
				implode(', ', $route->getMethods()),
				$action,
				implode(', ', $beforeFilters),
				implode(', ', $afterFilters),
				(string) $route->getName()
			];
		}

		$this->output->table(['Route', 'Allowed methods', 'Action', 'Before filters', 'After filters', 'Name'], $routes);
	}
}