<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\throttle\context\ContextInterface;
use mako\throttle\context\HttpUser;
use mako\throttle\RateLimiter;
use mako\throttle\RateLimiterInterface;
use mako\throttle\store\Redis;
use mako\throttle\store\StoreInterface;

/**
 * Rate limiter service.
 */
class RateLimiterService extends Service
{
	/**
	 * {@inheritDoc}
	 */
	public function register(): void
	{
		$this->container->register(StoreInterface::class, Redis::class);

		$this->container->register(ContextInterface::class, HttpUser::class);

		$this->container->registerSingleton([RateLimiterInterface::class, 'rateLimiter'], RateLimiter::class);
	}
}
