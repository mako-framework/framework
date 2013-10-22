<?php

namespace mako\http\routing;

use \Closure;
use \RuntimeException;
use \mako\http\Request;
use \mako\http\Response;
use \mako\http\routing\Routes;
use \mako\http\routing\Controller;

/**
 * Route dispatcher.
 * 
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Dispatcher
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

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
	 * Route.
	 * 
	 * @var \mako\http\routing\Route
	 */

	protected $route;

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
	 * @param   \mako\http\Request  $request  Request
	 */

	public function __construct(Request $request)
	{
		$this->request = $request;

		$this->route = $request->route();

		$this->response = new Response();
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
		if($filter instanceof Closure)
		{
			return $filter($this->request, $this->response);
		}
		else
		{
			$filter = Routes::getFilter($filter);

			return $filter($this->request, $this->response);
		}
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
	 * @return  mixed
	 */

	protected function dispatchClosure(Closure $closure)
	{
		return call_user_func_array($closure, array_merge(array($this->request, $this->response), $this->route->getParameters()));
	}

	/**
	 * Dispatch a controller action.
	 * 
	 * @access  protected
	 * @param   string     $controller  Controller
	 * @return  mixed
	 */

	protected function dispatchController($controller)
	{
		list($controller, $method) = explode('::', $controller, 2);

		$controller = new $controller($this->request, $this->response);

		if(!($controller instanceof Controller))
		{
			throw new RuntimeException(vsprintf("%s(): All controllers must extend the mako\http\\routing\Controller class.", array(__METHOD__)));
		}

		$returnValue = $controller->beforeFilter();

		if(empty($returnValue))
		{
			// The before filter didn't return any data so we can execute the route action and after filter

			$returnValue = call_user_func_array(array($controller, $method), $this->route->getParameters());

			$controller->afterFilter();
		}
		else
		{
			// The before filter returned data so we need to skip the after filters

			$this->skipAfterFilters = true;
		}

		return $returnValue;
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
				$this->response->body($this->dispatchClosure($action));
			}
			else
			{
				$this->response->body($this->dispatchController($action));
			}

			if(!$this->skipAfterFilters)
			{
				$this->afterFilters();
			}
		}

		return $this->response;
	}
}

/** -------------------- End of file -------------------- **/