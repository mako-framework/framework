<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing;

use mako\http\Request;

use function http_build_query;
use function preg_match;
use function preg_replace;
use function str_contains;

/**
 * URL builder.
 */
class URLBuilder
{
	/**
	 * Base URL.
	 */
	protected string $baseURL;

	/**
	 * Script name.
	 */
	protected string $scriptName;

	/**
	 * Language prefix.
	 */
	protected string $languagePrefix = '';

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Request $request,
		protected Routes $routes,
		protected bool $cleanURLs = false,
		?string $baseURL = null
	) {
		$this->baseURL = $baseURL ?? $this->request->getBaseURL();

		$this->scriptName = $request->getScriptName();

		if (!empty($language = $request->getLanguagePrefix())) {
			$this->languagePrefix = "/{$language}";
		}
	}

	/**
	 * Returns TRUE if the pattern matches the current route and FALSE if not.
	 */
	public function matches(string $pattern): bool
	{
		return preg_match("#{$pattern}#", $this->request->getPath()) === 1;
	}

	/**
	 * Returns the base URL of the application.
	 */
	public function base(): string
	{
		return $this->baseURL;
	}

	/**
	 * Returns the URL of the specified path.
	 */
	public function to(string $path, array $queryParams = [], string $separator = '&', bool|string $language = true): string
	{
		$url = $this->baseURL . ($this->cleanURLs ? '' : "/{$this->scriptName}") . ($language === true ? $this->languagePrefix : (!$language ? '' : "/{$language}")) . $path;

		if (!empty($queryParams)) {
			$url .= '?' . http_build_query($queryParams, arg_separator: $separator, encoding_type: PHP_QUERY_RFC3986);
		}

		return $url;
	}

	/**
	 * Returns the URL of a named route.
	 */
	public function toRoute(string $routeName, array $routeParams = [], array $queryParams = [], string $separator = '&', bool|string $language = true): string
	{
		$route = $this->routes->getNamedRoute($routeName)->getRoute();

		foreach ($routeParams as $key => $value) {
			if (empty($value) && ($value === '' || $value === false || $value === null)) {
				continue;
			}

			$route = preg_replace("/{{$key}}\??/", $value, $route, 1);
		}

		if (str_contains($route, '?')) {
			$route = preg_replace('/\/{\w+}\?/', '', $route);
		}

		return $this->to($route, $queryParams, $separator, $language);
	}

	/**
	 * Returns the current URL of the request.
	 */
	public function current(?array $queryParams = [], string $separator = '&', bool|string $language = true): string
	{
		$queryParams = $queryParams === null ? [] : ($queryParams ?: $this->request->query->all());

		return $this->to($this->request->getPath(), $queryParams, $separator, $language);
	}

	/**
	 * Returns the URL of the specified route.
	 */
	public function toLanguage(string $route, string $language, array $queryParams = [], string $separator = '&'): string
	{
		return $this->to($route, $queryParams, $separator, $language);
	}

	/**
	 * Returns the URL of a named route.
	 */
	public function toRouteLanguage(string $routeName, string $language, array $routeParams = [], array $queryParams = [], string $separator = '&'): string
	{
		return $this->toRoute($routeName, $routeParams, $queryParams, $separator, $language);
	}

	/**
	 * Returns the current URL of the request.
	 */
	public function currentLanguage(string $language, ?array $queryParams = [], string $separator = '&'): string
	{
		return $this->current($queryParams, $separator, $language);
	}
}
