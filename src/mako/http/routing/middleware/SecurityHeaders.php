<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing\middleware;

use Closure;
use mako\http\Request;
use mako\http\Response;

/**
 * Security headers middleware.
 *
 * @author Frederic G. Østby
 */
class SecurityHeaders implements MiddlewareInterface
{
	/**
	 * Security headers.
	 *
	 * @var array
	 */
	protected $headers =
	[
		'X-Content-Type-Options' => 'nosniff',
		'X-Frame-Options'        => 'sameorigin',
		'X-XSS-Protection'       => '1; mode=block',
	];

	/**
	 * Returns an array containing the security headers we want to set.
	 *
	 * @return array
	 */
	protected function getHeaders(): array
	{
		return $this->headers;
	}

	/**
	 * {@inheritdoc}
	 */
	public function execute(Request $request, Response $response, Closure $next): Response
	{
		$headers = $response->getHeaders();

		foreach($this->getHeaders() as $name => $value)
		{
			$headers->add($name, $value);
		}

		return $next($request, $response);
	}
}
