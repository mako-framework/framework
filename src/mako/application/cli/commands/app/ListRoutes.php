<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\app;

use Closure;
use mako\http\routing\Route;
use mako\http\routing\Routes;
use mako\reactor\Command;

use function implode;

/**
 * Command that lists all registered routes.
 */
class ListRoutes extends Command
{
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Lists all registered routes.';

	/**
	 * Returns a normalzed action name.
	 *
	 * @param  \mako\http\routing\Route $route Route
	 * @return string
	 */
	protected function getNormalizedActionName(Route $route): string
	{
		$action = $route->getAction();

		if($action instanceof Closure)
		{
			return 'Closure';
		}
		elseif(is_array($action))
		{
			[$class, $method] = $action;

			return "[{$class}::class, '{$method}']";
		}

		return $action;
	}

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
			$routeCollection[] =
			[
				$route->getRoute(),
				implode(', ', $route->getMethods()),
				$this->getNormalizedActionName($route),
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
