<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\http\routing;

use mako\http\Request;
use mako\http\routing\Routes;

/**
 * URL builder.
 *
 * @author  Frederic G. Østby
 */

class URLBuilder
{
	/**
	 * Request instance.
	 *
	 * @var \mako\http\Request
	 */

	protected $request;

	/**
	 * Route collection.
	 *
	 * @var \mako\http\routing\Routes
	 */

	protected $routes;

	/**
	 * Create "clean" URLs?
	 *
	 * @var boolean
	 */

	protected $cleanURLs;

	/**
	 * Language prefix.
	 *
	 * @var string
	 */

	protected $languagePrefix;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\http\Request         $request    Request instance
	 * @param   \mako\http\routing\Routes  $routes     Route collection
	 * @param   boolean                    $cleanURLs  Create "clean" URLs?
	 */

	public function __construct(Request $request, Routes $routes, $cleanURLs = false)
	{
		$this->request   = $request;
		$this->routes    = $routes;
		$this->cleanURLs = $cleanURLs;

		$language = $request->languagePrefix();

		if(!empty($language))
		{
			$this->languagePrefix = '/' . $language;
		}
	}

	/**
	 * Returns TRUE if the pattern matches the current route and FALSE if not.
	 *
	 * @access  public
	 * @param   string   $pattern  Pattern to match
	 * @return  boolean
	 */

	public function matches($pattern)
	{
		return (bool) preg_match('#' . $pattern . '#', $this->request->path());
	}

	/**
	 * Returns the base URL of the application.
	 *
	 * @access  public
	 * @return  string
	 */

	public function base()
	{
		return $this->request->baseURL();
	}

	/**
	 * Returns the URL of the specified path.
	 *
	 * @access  public
	 * @param   string   $path         Path
	 * @param   array    $queryParams  Associative array used to build URL-encoded query string
	 * @param   string   $separator    Argument separator
	 * @param   mixed    $language     Request language
	 * @return  string
	 */

	public function to($path, array $queryParams = [], $separator = '&amp;', $language = true)
	{
		$url = $this->base() . ($this->cleanURLs ? '' : '/index.php') . ($language === true ? $this->languagePrefix : (!$language ? '' : '/' . $language)) . $path;

		if(!empty($queryParams))
		{
			$url .= '?' . http_build_query($queryParams, '', $separator);
		}

		return $url;
	}

	/**
	 * Returns the URL of a named route.
	 *
	 * @access  public
	 * @param   string  $routeName    Route name
	 * @param   array   $routeParams  Route parameters
	 * @param   array   $queryParams  Associative array used to build URL-encoded query string
	 * @param   string  $separator    Argument separator
	 * @param   mixed   $language     Request language
	 * @return  string
	 */

	public function toRoute($routeName, array $routeParams = [], array $queryParams = [], $separator = '&amp;', $language = true)
	{
		$route = $this->routes->getNamedRoute($routeName)->getRoute();

		foreach($routeParams as $key => $value)
		{
			if(!empty($value))
			{
				$route = preg_replace('/{' . $key . '}\??/', $value, $route, 1);
			}
		}

		if(strpos($route, '?') !== false)
		{
			$route = preg_replace('/\/{\w+}\?/', '', $route);
		}

		return $this->to($route, $queryParams, $separator, $language);
	}

	/**
	 * Returns the current URL of the request.
	 *
	 * @access  public
	 * @param   array    $queryParams  Associative array used to build URL-encoded query string
	 * @param   string   $separator    Argument separator
	 * @param   mixed    $language     Request language
	 * @return  string
	 */

	public function current(array $queryParams = [], $separator = '&amp;', $language = true)
	{
		$queryParams = $queryParams ?: $this->request->get();

		return $this->to($this->request->path(), $queryParams, $separator, $language);
	}

	/**
	 * Returns the URL of the specified route.
	 *
	 * @access  public
	 * @param   string   $route        URL segments
	 * @param   mixed    $language     Request language
	 * @param   array    $queryParams  Associative array used to build URL-encoded query string
	 * @param   string   $separator    Argument separator
	 * @return  string
	 */

	public function toLanguage($route, $language, array $queryParams = [], $separator = '&amp;')
	{
		return $this->to($route, $queryParams, $separator, $language);
	}

	/**
	 * Returns the URL of a named route.
	 *
	 * @access  public
	 * @param   string  $routeName    Route name
	 * @param   string  $language     Request language
	 * @param   array   $routeParams  Route parameters
	 * @param   array   $queryParams  Associative array used to build URL-encoded query string
	 * @param   string  $separator    Argument separator
	 * @return  string
	 */

	public function toRouteLanguage($routeName, $language, array $routeParams = [], array $queryParams = [], $separator = '&amp;')
	{
		return $this->toRoute($routeName, $routeParams, $queryParams, $separator, $language);
	}

	/**
	 * Returns the current URL of the request.
	 *
	 * @access  public
	 * @param   string  $language     Request language
	 * @param   array   $queryParams  Query parameters
	 * @param   string  $separator    Argument separator
	 * @return  string
	 */

	public function currentLanguage($language, array $queryParams = [], $separator = '&amp;')
	{
		return $this->current($queryParams, $separator, $language);
	}
}