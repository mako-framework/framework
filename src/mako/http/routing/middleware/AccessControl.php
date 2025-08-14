<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing\middleware;

use Closure;
use mako\http\Request;
use mako\http\Response;
use Override;

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
		return $request->headers->get('Origin');
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
	#[Override]
	public function execute(Request $request, Response $response, Closure $next): Response
	{
		if ($this->allowsCredentials()) {
			$response->headers->add('Access-Control-Allow-Credentials', 'true');
		}

		$origin = $this->getOrigin($request);

		if ($origin !== null && $this->isDomainAllowed($origin)) {
			$response->headers->add('Access-Control-Allow-Origin', $origin);

			$response->headers->add('Vary', 'Origin', false);
		}
		elseif ($this->allowsAllDomains()) {
			$response->headers->add('Access-Control-Allow-Origin', '*');
		}

		if (!empty($allowedHeaders = $this->getAllowedHeaders())) {
			$response->headers->add('Access-Control-Allow-Headers', implode(', ', $allowedHeaders));
		}

		if (!empty($allowedMethods = $this->getAllowedMethods())) {
			$response->headers->add('Access-Control-Allow-Methods', implode(', ', $allowedMethods));
		}

		return $next($request, $response);
	}
}
