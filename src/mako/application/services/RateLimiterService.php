<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use Closure;
use mako\throttle\context\ContextInterface;
use mako\throttle\context\HttpUser;
use mako\throttle\RateLimiter;
use mako\throttle\RateLimiterInterface;
use mako\throttle\stores\APCu;
use mako\throttle\stores\StoreInterface;

/**
 * Rate limiter service.
 */
class RateLimiterService extends Service
{
	/**
	 * Returns the store.
	 */
	protected function getStore(): Closure|string
	{
		return APCu::class;
	}

	/**
	 * Returns the context.
	 */
	protected function getContext(): Closure|string
	{
		return HttpUser::class;
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void
	{
		$this->container->register(StoreInterface::class, $this->getStore());

		$this->container->register(ContextInterface::class, $this->getContext());

		$this->container->registerSingleton([RateLimiterInterface::class, 'rateLimiter'], RateLimiter::class);
	}
}
