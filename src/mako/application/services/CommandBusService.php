<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\commander\CommandBus;

/**
 * Command bus service.
 *
 * @author  Yamada Taro
 */

class CommandBusService extends Service
{
	/**
	 * {@inheritdoc}
	 */

	public function register()
	{
		$this->container->registerSingleton(['mako\commander\CommandBusInterface', 'bus'], function($container)
		{
			return new CommandBus($container);
		});
	}
}