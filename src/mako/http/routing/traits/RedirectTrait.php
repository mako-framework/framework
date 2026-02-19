<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing\traits;

use mako\http\response\senders\Redirect;

/**
 * Redirect trait.
 *
 * @property \mako\http\routing\Routes     $routes
 * @property \mako\http\routing\UrlBuilder $urlBuilder
 */
trait RedirectTrait
{
	/**
	 * Returns a redirect response container.
	 */
	protected function redirect(string $location, array $routeParams = [], array $queryParams = [], string $separator = '&', bool|string $language = true): Redirect
	{
		if ($this->routes->hasNamedRoute($location)) {
			$location = $this->urlBuilder->toRoute($location, $routeParams, $queryParams, $separator, $language);
		}

		return new Redirect($location);
	}
}
