<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\event\Event;

/**
 * Event service.
 *
 * @deprecated
 */
class EventService extends Service
{
	/**
	 * {@inheritDoc}
	 */
	public function register(): void
	{
		$this->container->registerSingleton([Event::class, 'event'], static fn ($container) => new Event($container));
	}
}
