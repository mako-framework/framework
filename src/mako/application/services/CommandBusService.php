<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\commander\CommandBus;
use mako\commander\CommandBusInterface;

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
		$this->container->registerSingleton([CommandBusInterface::class, 'commander'], function($container)
		{
			return new CommandBus($container);
		});
	}
}