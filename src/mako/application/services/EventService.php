<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\event\Event;

/**
 * Event service.
 *
 * @author  Frederic G. Østby
 */

class EventService extends Service
{
	/**
	 * {@inheritdoc}
	 */

	public function register()
	{
		$this->container->registerSingleton(['mako\event\Event', 'event'], function($container)
		{
			return new Event($container);
		});
	}
}