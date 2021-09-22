<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing\middleware;

use Closure;
use mako\http\Request;
use mako\http\Response;

use function implode;
use function in_array;

/**
 * Access control middleware.
 */
abstract class AccessControl implements MiddlewareInterface
{
	/**
	 * Allow credentials?
	 *
	 * @var bool
	 */
	protected $allowCredentials = false;

	/**
	 * Allow all domains?
	 *
	 * @var bool
	 */
	protected $allowAllDomains = false;

	/**
	 * Allowed domains.
	 *
	 * @var array
	 */
	protected $allowedDomains = [];

	/**
	 * @var array
	 */
	protected $allowedHeaders = [];

	/**
	 * @var array
	 */
	protected $allowedMethods = [];

	/**
	 * Returns TRUE if we allows credentials and FALSE if not.
	 *
	 * @return bool
	 */
	protected function allowsCredentials(): bool
	{
		return $this->allowCredentials;
	}

	/**
	 * Returns TRUE if we allow all domains and FALSE if not.
	 *
	 * @return bool
	 */
	protected function allowsAllDomains(): bool
	{
		return $this->allowAllDomains;
	}

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
	 * Returns the allowed headers.
	 *
	 * @return array
	 */
	protected function getAllowedHeaders(): array
	{
		return $this->allowedHeaders;
	}

	/**
	 * Returns the allowed methods.
	 *
	 * @return array
	 */
	protected function getAllowedMethods(): array
	{
		return $this->allowedMethods;
	}

	/**
	 * {@inheritDoc}
	 */
	public function execute(Request $request, Response $response, Closure $next): Response
	{
		if($this->allowsCredentials())
		{
			$response->getHeaders()->add('Access-Control-Allow-Credentials', 'true');
		}

		if($this->allowsAllDomains())
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

		if(!empty($allowedHeaders = $this->getAllowedHeaders()))
		{
			$response->getHeaders()->add('Access-Control-Allow-Headers', implode(', ', $allowedHeaders));
		}

		if(!empty($allowedMethods = $this->getAllowedMethods()))
		{
			$response->getHeaders()->add('Access-Control-Allow-Methods', implode(', ', $allowedMethods));
		}

		return $next($request, $response);
	}
}
