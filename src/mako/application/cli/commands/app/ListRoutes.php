<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\app;

use Closure;
use mako\http\routing\Routes;
use mako\reactor\Command;

use function implode;

/**
 * Command that lists all registered routes.
 *
 * @author Frederic G. Østby
 */
class ListRoutes extends Command
{
	/**
	 * Make the command strict.
	 *
	 * @var bool
	 */
	protected $isStrict = true;

	/**
	 * Command information.
	 *
	 * @var array
	 */
	protected $commandInformation =
	[
		'description' => 'Lists all registered routes.',
	];

	/**
	 * Executes the command.
	 *
	 * @param \mako\http\routing\Routes $routes Route collection
	 */
	public function execute(Routes $routes): void
	{
		$routeCollection = [];

		// Build table rows

		foreach($routes->getRoutes() as $route)
		{
			// Normalize action name

			$action = ($route->getAction() instanceof Closure) ? 'Closure' : $route->getAction();

			// Build table row

			$routeCollection[] =
			[
				$route->getRoute(),
				implode(', ', $route->getMethods()),
				$action,
				implode(', ', $route->getMiddleware()),
				implode(', ', $route->getConstraints()),
				(string) $route->getName(),
			];
		}

		// Draw table

		$headers =
		[
			'<green>Route</green>',
			'<green>Allowed methods</green>',
			'<green>Action</green>',
			'<green>Middleware *</green>',
			'<green>Constraints **</green>',
			'<green>Name</green>',
		];

		$this->table($headers, $routeCollection);

		$this->write('<green>*</green> <faded>Global middleware is not listed.</faded>');
		$this->write('<green>**</green> <faded>Global constraints are not listed.</faded>');
	}
}
