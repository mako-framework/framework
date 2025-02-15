<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\throttle;

use DateTimeInterface;

/**
 * Rate limiter interface.
 */
interface RateLimiterInterface
{
	/**
	 * Returns TRUE if the rate limit has been reached for the action and FALSE if not.
	 */
	public function isLimitReached(string $action, int $maxAttempts): bool;

	/**
	 * Returns the number of remaining attempts for the action.
	 */
	public function getRemaining(string $action, int $maxAttempts): int;

	/**
	 * Returns the date and time when the action can be retried.
	 */
	public function getRetryAfter(string $action): ?DateTimeInterface;

	/**
	 * Increments the number of attempts for the action.
	 */
	public function increment(string $action, DateTimeInterface $expiresAt): int;
}
