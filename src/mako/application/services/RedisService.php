<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\redis\ConnectionManager;

/**
 * Redis service.
 *
 * @author Frederic G. Østby
 */
class RedisService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->container->registerSingleton([ConnectionManager::class, 'redis'], function($container)
		{
			$config = $container->get('config')->get('redis');

			return new ConnectionManager($config['default'], $config['configurations']);
		});
	}
}
