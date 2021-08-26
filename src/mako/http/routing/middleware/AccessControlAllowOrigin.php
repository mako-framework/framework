<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing\middleware;

use Closure;
use mako\http\Request;
use mako\http\Response;

use function in_array;

/**
 * Access control allow origin middleware.
 *
 * @deprecated
 */
abstract class AccessControlAllowOrigin implements MiddlewareInterface
{
	/**
	 * Allow all domains?
	 *
	 * @var bool
	 */
	protected $allowAll = false;

	/**
	 * Allowed domains.
	 *
	 * @var array
	 */
	protected $allowedDomains = [];

	/**
	 * Returns the request origin.
	 *
	 * @param  \mako\http\Request $request Request instance
	 * @return string|null
	 */
	protected function getOrigin(Request $request): ?string
	{
		return $request->getHeaders()->get('Origin');
	}

	/**
	 * Returns TRUE if the domain is allowed and FALSE if not.
	 *
	 * @param  string $domain Domain
	 * @return bool
	 */
	protected function isDomainAllowed(string $domain): bool
	{
		return in_array($domain, $this->allowedDomains, true);
	}

	/**
	 * {@inheritDoc}
	 */
	public function execute(Request $request, Response $response, Closure $next): Response
	{
		if($this->allowAll)
		{
			$response->getHeaders()->add('Access-Control-Allow-Origin', '*');
		}
		else
		{
			$origin = $this->getOrigin($request);

			if($origin !== null && $this->isDomainAllowed($origin))
			{
				$response->getHeaders()->add('Access-Control-Allow-Origin', $origin);

				$response->getHeaders()->add('Vary', 'Origin', false);
			}
		}

		return $next($request, $response);
	}
}
