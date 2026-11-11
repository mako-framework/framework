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

/**
 * Security headers middleware.
 */
class SecurityHeaders implements MiddlewareInterface
{
	/**
	 * Security headers.
	 */
	protected array $headers = [
		'X-Content-Type-Options' => 'nosniff',
		'X-Frame-Options'        => 'sameorigin',
	];

	/**
	 * Returns an array containing the security headers we want to set.
	 */
	protected function getHeaders(): array
	{
		return $this->headers;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function execute(Request $request, Response $response, Closure $next): Response
	{
		foreach ($this->getHeaders() as $name => $value) {
			$response->headers->add($name, $value);
		}

		return $next($request, $response);
	}
}
