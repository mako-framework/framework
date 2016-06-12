<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\app;

use Closure;

use mako\application\Application;
use mako\file\FileSystem;
use mako\reactor\Command;
use mako\utility\Str;

/**
 * Command that lists all registered routes.
 *
 * @author  Frederic G. Østby
 */
class ListRoutes extends Command
{
	/**
	 * Command information.
	 *
	 * @var array
	 */
	protected $commandInformation =
	[
		'description' => 'Lists all registered routes.',
		'arguments'   => [],
		'options'     => [],
	];

	/**
	 * Executes the command.
	 *
	 * @access  public
	 * @param   \mako\application\Application  $application  Application instance
	 */
	public function execute(Application $application)
	{
		// Build table rows

		foreach($application->getRouteCollection()->getRoutes() as $route)
		{
			// Normalize action name

			$action = ($route->getAction() instanceof Closure) ? 'Closure' : $route->getAction();

			// Build table row

			$routes[] =
			[
				$route->getRoute(),
				implode(', ', $route->getMethods()),
				$action,
				implode(', ', $route->getMiddleware()),
				(string) $route->getName()
			];
		}

		// Draw table

		$headers =
		[
			'<green>Route</green>',
			'<green>Allowed methods</green>',
			'<green>Action</green>',
			'<green>Middleware</green>',
			'<green>Name</green>',
		];

		$this->table($headers, $routes);
	}
}