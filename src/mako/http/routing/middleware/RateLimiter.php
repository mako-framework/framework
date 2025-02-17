<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing\middleware;

use Closure;
use DateInterval;
use mako\http\exceptions\TooManyRequestsException;
use mako\http\Request;
use mako\http\Response;
use mako\throttle\RateLimiterInterface;

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
		protected DateInterval|string $resetAfter,
		protected bool $setRateLimitHeaders = true,
		protected ?string $action = null
	) {
	}

	/**
	 * Returns the expiration interval.
	 */
	protected function getResetAfter(): DateInterval
	{
		if ($this->resetAfter instanceof DateInterval) {
			return $this->resetAfter;
		}

		return DateInterval::createFromDateString($this->resetAfter);
	}

	/**
	 * Returns the action.
	 */
	protected function getAction(Request $request): string
	{
		return $request->getRoute()->getRoute();
	}

	/**
	 * {@inheritDoc}
	 */
	public function execute(Request $request, Response $response, Closure $next): Response
	{
		$action = $this->action ?? $this->getAction($request);

		if ($this->rateLimiter->isLimitReached($action, $this->maxRequests)) {
			throw new TooManyRequestsException(retryAfter: $this->rateLimiter->getRetryAfter($action));
		}

		$hits = $this->rateLimiter->increment($action, $this->getResetAfter());

		if ($this->setRateLimitHeaders) {
			$response->headers->add('X-RateLimit-Limit', $this->maxRequests);
			$response->headers->add('X-RateLimit-Remaining', $this->maxRequests - $hits);
			$response->headers->add('X-RateLimit-Reset', $this->rateLimiter->getRetryAfter($action)->getTimestamp());
		}

		return $next($request, $response);
	}
}
