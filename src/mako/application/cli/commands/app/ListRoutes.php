<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\app;

use Closure;
use mako\cli\input\arguments\Argument;
use mako\cli\input\arguments\NamedArgument;
use mako\cli\output\components\Alert;
use mako\http\routing\Dispatcher;
use mako\http\routing\Route;
use mako\http\routing\Router;
use mako\http\routing\Routes;
use mako\reactor\attributes\CommandArguments;
use mako\reactor\attributes\CommandDescription;
use mako\reactor\Command;
use mako\utility\Arr;

use function array_keys;
use function array_map;
use function implode;
use function is_array;
use function max;
use function mb_strwidth;
use function str_contains;
use function str_pad;

/**
 * Command that lists all registered routes.
 */
#[CommandDescription('Lists all registered routes.')]
#[CommandArguments(
	new NamedArgument('filter', 'f', 'Filter routes using the route action, name or path', Argument::IS_OPTIONAL),
	new NamedArgument('detailed', 'd', 'Show more information about the route', Argument::IS_BOOL | Argument::IS_OPTIONAL),
)]
class ListRoutes extends Command
{
	/**
	 * Returns a normalzed action name.
	 */
	protected function getNormalizedActionName(Route $route): string
	{
		$action = $route->getAction();

		if ($action instanceof Closure) {
			return 'Closure';
		}
		elseif (is_array($action)) {
			[$class, $method] = $action;

			return "[{$class}::class, '{$method}']";
		}

		return $action;
	}

	/**
	 * Returns TRUE if the route matches the filter and FALSE if not.
	 */
	protected function routeMatches(string $filter, Route $route): bool
	{
		if (str_contains($route->getRoute(), $filter)) {
			return true;
		}

		if (str_contains($this->getNormalizedActionName($route), $filter)) {
			return true;
		}

		if (str_contains((string) $route->getName(), $filter)) {
			return true;
		}

		return false;
	}

	/**
	 * Executes the command.
	 */
	public function execute(Routes $routes, Dispatcher $dispatcher, Router $router, bool $detailed = false, ?string $filter = null): void
	{
		$this->clear();

		$matched = 0;

		/** @var Route $route */
		foreach ($routes->getRoutes() as $route) {
			if ($filter !== null && !$this->routeMatches($filter, $route)) {
				continue;
			}

			$matched++;

			$middleware = (function ($middleware) {
				/** @var Dispatcher $this */
				return $this->orderMiddlewareByPriority($middleware);
			})
			->bindTo($dispatcher, Dispatcher::class)([
				...$dispatcher->getGlobalMiddleware(),
				...$route->getMiddleware(),
			]);

			$constraints = [
				...$router->getGlobalConstraints(),
				...$route->getConstraints(),
			];

			$methods     = implode(', ', $route->getMethods());
			$middleware  = implode(', ', Arr::pluck($middleware, 'middleware'));
			$constraints = implode(', ', $constraints);
			$name        = $route->getName();

			$this->alert("Path: {$route->getRoute()}", Alert::INFO);

			$this->nl();

			$labelAndValues = [];

			$labelAndValues['Methods'] = $methods;

			$labelAndValues['Action'] = $this->getNormalizedActionName($route);

			if (!empty($middleware)) {
				$labelAndValues['Middleware'] = $middleware;
			}

			if (!empty($constraints)) {
				$labelAndValues['Constraints'] = $constraints;
			}

			if (!empty($name)) {
				$labelAndValues['Name'] = $name;
			}

			if ($detailed) {
				$labelAndValues['Pattern'] = $this->output->formatter->escape($route->getRegex());
			}

			$maxLabelLength = max(array_map(mb_strwidth(...), array_keys($labelAndValues)));

			foreach ($labelAndValues as $label => $value) {
				$label = str_pad($label, $maxLabelLength, ' ');

				$this->write("<bold>{$label}:</bold> {$value}");
			}

			$this->nl();
		}

		if ($filter !== null) {
			$routes = $matched === 1 ? 'route' : 'routes';

			$this->write("<green>{$matched}</green> {$routes} matched the '<yellow>{$filter}</yellow>' filter.");

			$this->nl();
		}
	}
}
