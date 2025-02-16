<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\throttle;

use DateTimeInterface;
use mako\throttle\context\ContextInterface;
use mako\throttle\store\StoreInterface;

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
	public function isLimitReached(string $action, int $maxAttempts): bool
	{
		return $this->store->getHits($this->getKey($action)) >= $maxAttempts;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRemaining(string $action, int $maxAttempts): int
	{
		return $maxAttempts - $this->store->getHits($this->getKey($action));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRetryAfter(string $action): ?DateTimeInterface
	{
		return $this->store->getExpiration($this->getKey($action));
	}

	/**
	 * {@inheritDoc}
	 */
	public function increment(string $action, DateTimeInterface $expiresAt): int
	{
		return $this->store->increment($this->getKey($action), $expiresAt);
	}
}
