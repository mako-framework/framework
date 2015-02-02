<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\http\routing;

use Closure;

use mako\common\FunctionParserTrait;
use mako\http\Request;
use mako\http\Response;
use mako\http\routing\Route;
use mako\syringe\Container;

/**
 * Route dispatcher.
 *
 * @author  Frederic G. Østby
 */

class Dispatcher
{
	use FunctionParserTrait;

	/**
	 * Request.
	 *
	 * @var \mako\http\Request
	 */

	protected $request;

	/**
	 * Response.
	 *
	 * @var \mako\http\Response
	 */

	protected $response;

	/**
	 * Route filters.
	 *
	 * @var \mako\http\routing\Filters
	 */

	protected $filters;


	/**
	 * Route to be dispatched.
	 *
	 * @var \mako\http\routing\Route
	 */

	protected $route;

	/**
	 * Route parameters.
	 *
	 * @var array
	 */

	protected $parameters;

	/**
	 * IoC container instance.
	 *
	 * @var \mako\syringe\Container
	 */

	protected $container;

	/**
	 * Should the after filters be skipped?
	 *
	 * @var boolean
	 */

	protected $skipAfterFilters = false;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\http\Request          $request     Request instance
	 * @param   \mako\http\Response         $response    Response instance
	 * @param   \mako\http\routing\Filters  $filters     Filter collection
	 * @param   \mako\http\routing\Route    $route       The route we're dispatching
	 * @param   array                       $parameters  Route parameters
	 * @param   \mako\syringe\Container     $container   IoC container
	 */

	public function __construct(Request $request, Response $response, Filters $filters, Route $route, array $parameters = [], Container $container = null)
	{
		$this->request = $request;

		$this->response = $response;

		$this->filters = $filters;

		$this->route = $route;

		$this->parameters = $parameters;

		$this->container = $container ?: new Container;
	}

	/**
	 * Resolves the filter.
	 *
	 * @access  protected
	 * @param   string         $filter  Filter
	 * @return  array|Closure
	 */

	protected function resolveFilter($filter)
	{
		$filter = $this->filters->get($filter);

		if(!($filter instanceof Closure))
		{
			$filter = [$this->container->get($filter), 'filter'];
		}

		return $filter;
	}

	/**
	 * Executes a filter.
	 *
	 * @access  protected
	 * @param   string|\Closure  $filter  Filter
	 * @return  mixed
	 */

	protected function executeFilter($filter)
	{
		// Parse the filter function call

		list($filter, $parameters) = $this->parseFunction($filter);

		// Get the filter from the filter collection

		$filter = $this->resolveFilter($filter);

		// Execute the filter and return its return value

		return $this->container->call($filter, $parameters);
	}

	/**
	 * Executes before filters.
	 *
	 * @access  protected
	 * @return  mixed
	 */

	protected function beforeFilters()
	{
		foreach($this->route->getBeforeFilters() as $filter)
		{
			$returnValue = $this->executeFilter($filter);

			if(!empty($returnValue))
			{
				return $returnValue;
			}
		}
	}

	/**
	 * Executes after filters.
	 *
	 * @access  protected
	 */

	protected function afterFilters()
	{
		foreach($this->route->getAfterFilters() as $filter)
		{
			$this->executeFilter($filter);
		}
	}

	/**
	 * Dispatch a closure controller action.
	 *
	 * @access  protected
	 * @param   \Closure   $closure  Closure
	 */

	protected function dispatchClosure(Closure $closure)
	{
		$this->response->body($this->container->call($closure, $this->parameters));
	}

	/**
	 * Dispatch a controller action.
	 *
	 * @access  protected
	 * @param   string     $controller  Controller
	 */

	protected function dispatchController($controller)
	{
		list($controller, $method) = explode('::', $controller, 2);

		$controller = $this->container->get($controller);

		// Execute the before filter if we have one

		if(method_exists($controller, 'beforeFilter'))
		{
			$returnValue = $this->container->call([$controller, 'beforeFilter']);
		}

		if(empty($returnValue))
		{
			// The before filter didn't return any data so we can set the
			// response body to whatever the route action returns

			$this->response->body($this->container->call([$controller, $method], $this->parameters));

			// Execute the after filter if we have one

			if(method_exists($controller, 'afterFilter'))
			{
				$this->container->call([$controller, 'afterFilter']);
			}
		}
		else
		{
			// The before filter returned data so we'll set the response body to whatever it returned
			// and tell the dispatcher to skip all after filters

			$this->response->body($returnValue);

			$this->skipAfterFilters = true;
		}
	}

	/**
	 * Dispatches the route and returns the response.
	 *
	 * @access  public
	 * @return  \mako\http\Response
	 */

	public function dispatch()
	{
		$returnValue = $this->beforeFilters();

		if(!empty($returnValue))
		{
			$this->response->body($returnValue);
		}
		else
		{
			$action = $this->route->getAction();

			if($action instanceof Closure)
			{
				$this->dispatchClosure($action);
			}
			else
			{
				$this->dispatchController($action);
			}

			if(!$this->skipAfterFilters)
			{
				$this->afterFilters();
			}
		}

		return $this->response;
	}
}