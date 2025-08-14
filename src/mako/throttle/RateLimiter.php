<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\throttle;

use DateInterval;
use DateTime;
use DateTimeInterface;
use mako\throttle\context\ContextInterface;
use mako\throttle\stores\StoreInterface;
use Override;

/**
 * Rate limiter.
 */
class RateLimiter implements RateLimiterInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected StoreInterface $store,
		protected ContextInterface $context
	) {
	}

	/**
	 * Returns the rate limit key.
	 */
	protected function getKey(string $action): string
	{
		return "{$this->context->getIdentifier()}:{$action}";
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function isLimitReached(string $action, int $maxAttempts): bool
	{
		return $this->store->getHits($this->getKey($action)) >= $maxAttempts;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getRemaining(string $action, int $maxAttempts): int
	{
		return $maxAttempts - $this->store->getHits($this->getKey($action));
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getRetryAfter(string $action): ?DateTimeInterface
	{
		return $this->store->getExpiration($this->getKey($action));
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function increment(string $action, DateInterval $resetAfter): int
	{
		return $this->store->increment($this->getKey($action), (new DateTime)->add($resetAfter));
	}
}
