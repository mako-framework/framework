<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing\middleware;

use Closure;
use DateTime;
use DateTimeInterface;
use mako\http\exceptions\TooManyRequestsException;
use mako\http\Request;
use mako\http\Response;
use mako\throttle\RateLimiterInterface;

use function is_string;

/**
 * Rate limiter middleware.
 */
class RateLimiter implements MiddlewareInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected RateLimiterInterface $rateLimiter,
		protected int $maxRequests,
		protected DateTimeInterface|int|string $resetAfter,
		protected bool $setRateLimitHeaders = true,
	) {
	}

	/**
	 * Returns the expiration time.
	 */
	protected function getExpirationTime(): DateTimeInterface
	{
		if ($this->resetAfter instanceof DateTimeInterface) {
			return $this->resetAfter;
		}

		if (is_string($this->resetAfter)) {
			return new DateTime($this->resetAfter);
		}

		$expirationTime = new DateTime;

		$expirationTime->setTimestamp($expirationTime->getTimestamp() + $this->resetAfter);

		return $expirationTime;
	}

	/**
	 * Returns the action.
	 */
	protected function getAction(Request $request): string
	{
		return $request->getPath();
	}

	/**
	 * {@inheritDoc}
	 */
	public function execute(Request $request, Response $response, Closure $next): Response
	{
		$action = $this->getAction($request);

		if ($this->rateLimiter->isLimitReached($action, $this->maxRequests)) {
			throw new TooManyRequestsException($this->rateLimiter->getRetryAfter($action));
		}

		$hits = $this->rateLimiter->increment($action, $this->getExpirationTime());

		if ($this->setRateLimitHeaders) {
			$response->headers->add('X-RateLimit-Limit', $this->maxRequests);
			$response->headers->add('X-RateLimit-Remaining', $this->maxRequests - $hits);
			$response->headers->add('X-RateLimit-Reset', $this->rateLimiter->getRetryAfter($action)->getTimestamp());
		}

		return $next($request, $response);
	}
}
