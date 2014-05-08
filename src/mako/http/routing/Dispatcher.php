<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\http\routing;

use \Closure;
use \RuntimeException;
use \mako\http\Request;
use \mako\http\Response;
use \mako\http\routing\Route;
use \mako\http\routing\Routes;
use \mako\http\routing\Controller;
use \mako\syringe\Container;

/**
 * Route dispatcher.
 * 
 * @author  Frederic G. Østby
 */

class Dispatcher
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Route collection.
	 * 
	 * @var \mako\http\routing\Routes
	 */

	protected $routes;


	/**
	 * Route to be dispatched.
	 * 
	 * @var \mako\http\routing\Route
	 */

	protected $route;

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

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\http\routing\Routes  $routes    Route collection
	 * @param   \mako\http\routing\Route   $route     The route we're dispatching
	 * @param   \mako\http\Request         $request   Request instance
	 * @param   \mako\http\Response        $response  (optional) Response instance
	 * @param   \mako\syringe\Container    $container (optional) IoC container
	 */

	public function __construct(Routes $routes, Route $route, Request $request, Response $response = null, Container $container = null)
	{
		$this->routes     = $routes;
		$this->route      = $route;
		$this->request    = $request;
		$this->response   = $response ?: new Response($request);
		$this->container  = $container ?: new Container;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Executes a filter.
	 * 
	 * @access  protected
	 * @param   string|\Closure  $filter  Filter
	 * @return  mixed
	 */

	protected function executeFilter($filter)
	{
		if(($filter instanceof Closure) === false)
		{
			$filter = $this->routes->getFilter($filter);
		}

		return $filter($this->request, $this->response);
	}

	/**
	 * Executes before filters.
	 * 
	 * @access  protected
	 * @return  mixed
	 */

	protected function beforeFilters()
	{
		$returnValue = null;

		foreach($this->route->getBeforeFilters() as $filter)
		{
			$returnValue = $this->executeFilter($filter);

			if(!empty($returnValue))
			{
				break; // Stop further execution of filters if one of them return data
			}
		}

		return $returnValue;
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
		$this->response->body(call_user_func_array($closure, array_merge([$this->request, $this->response], $this->route->getParameters())));
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

		$controller = $this->container->get($controller, [$this->request, $this->response]);

		if(!($controller instanceof Controller))
		{
			throw new RuntimeException(vsprintf("%s(): All controllers must extend the mako\http\\routing\Controller class.", [__METHOD__]));
		}

		$returnValue = $controller->beforeFilter();

		if(empty($returnValue))
		{
			// The before filter didn't return any data so we can set the response body to whatever 
			// the route action returns before executing its after filter

			$this->response->body(call_user_func_array([$controller, $method], $this->route->getParameters()));

			$controller->afterFilter();
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
		// Add route headers to response

		foreach($this->route->getHeaders() as $name => $value)
		{
			$this->response->header($name, $value);
		}

		// Dispatch the request

		if($this->request->method() !== 'OPTIONS')
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
		}	

		return $this->response;
	}
}