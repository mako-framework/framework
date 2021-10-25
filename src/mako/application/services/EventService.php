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
 * @author Frederic G. Østby
 */
class EventService extends Service
{
	/**
	 * {@inheritDoc}
	 */
	public function register(): void
	{
		$this->container->registerSingleton([Event::class, 'event'], static function($container)
		{
			return new Event($container);
		});
	}
}
