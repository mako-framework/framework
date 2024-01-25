<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\app;

use Closure;
use mako\cli\input\arguments\Argument;
use mako\cli\output\helpers\Alert;
use mako\http\routing\Route;
use mako\http\routing\Routes;
use mako\reactor\Command;
use mako\utility\Arr;

use function array_keys;
use function array_map;
use function implode;
use function is_array;
use function max;
use function str_contains;
use function str_pad;

/**
 * Command that lists all registered routes.
 */
class ListRoutes extends Command
{
	/**
	 * {@inheritDoc}
	 */
	protected string $description = 'Lists all registered routes.';

	/**
	 * {@inheritDoc}
	 */
	public function getArguments(): array
	{
		return [
			new Argument('-f|--filter', 'Filter routes using the route action, name or path', Argument::IS_OPTIONAL),
			new Argument('-d|--detailed', 'Show more information about the route', Argument::IS_BOOL | Argument::IS_OPTIONAL),
		];
	}

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
	public function execute(Routes $routes, bool $detailed = false, ?string $filter = null): void
	{
		$this->clear();

		$this->nl();

		$matched = 0;

		/** @var \mako\http\routing\Route $route */
		foreach ($routes->getRoutes() as $route) {
			if ($filter !== null && !$this->routeMatches($filter, $route)) {
				continue;
			}

			$matched++;

			$methods     = implode(', ', $route->getMethods());
			$middleware  = implode(', ', Arr::pluck($route->getMiddleware(), 'middleware'));
			$constraints = implode(', ', $route->getConstraints());
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
				$labelAndValues['Pattern'] = $this->output->getFormatter()->escape($route->getRegex());
			}

			$maxLabelLength = max(array_map('mb_strwidth', array_keys($labelAndValues)));

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

		$this->write('<green>*</green>  <faded>Global middleware is not listed.</faded>');
		$this->write('<green>**</green> <faded>Global constraints are not listed.</faded>');

		$this->nl();
	}
}
