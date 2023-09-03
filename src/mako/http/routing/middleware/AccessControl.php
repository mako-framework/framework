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
	 */
	protected bool $allowCredentials = false;

	/**
	 * Allow all domains?
	 */
	protected bool $allowAllDomains = false;

	/**
	 * Allowed domains.
	 */
	protected array $allowedDomains = [];

	/**
	 * Allowed headers.
	 */
	protected array $allowedHeaders = [];

	/**
	 * Allowed methods.
	 */
	protected array $allowedMethods = [];

	/**
	 * Returns TRUE if we allows credentials and FALSE if not.
	 */
	protected function allowsCredentials(): bool
	{
		return $this->allowCredentials;
	}

	/**
	 * Returns TRUE if we allow all domains and FALSE if not.
	 */
	protected function allowsAllDomains(): bool
	{
		return $this->allowAllDomains;
	}

	/**
	 * Returns the request origin.
	 */
	protected function getOrigin(Request $request): ?string
	{
		return $request->getHeaders()->get('Origin');
	}

	/**
	 * Returns the allowed domains.
	 */
	protected function getAllowedDomains(): array
	{
		return $this->allowedDomains;
	}

	/**
	 * Returns TRUE if the domain is allowed and FALSE if not.
	 */
	protected function isDomainAllowed(string $domain): bool
	{
		return in_array($domain, $this->getAllowedDomains(), true);
	}

	/**
	 * Returns the allowed headers.
	 */
	protected function getAllowedHeaders(): array
	{
		return $this->allowedHeaders;
	}

	/**
	 * Returns the allowed methods.
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

		$origin = $this->getOrigin($request);

		if($origin !== null && $this->isDomainAllowed($origin))
		{
			$response->getHeaders()->add('Access-Control-Allow-Origin', $origin);

			$response->getHeaders()->add('Vary', 'Origin', false);
		}
		elseif($this->allowsAllDomains())
		{
			$response->getHeaders()->add('Access-Control-Allow-Origin', '*');
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
