<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\application\services\Service;
use mako\redis\ConnectionManager;

/**
 * Redis service.
 *
 * @author  Frederic G. Østby
 */

class RedisService extends Service
{
	/**
	 * {@inheritdoc}
	 */

	public function register()
	{
		$this->container->registerSingleton(['mako\redis\ConnectionManager', 'redis'], function($container)
		{
			$config = $container->get('config')->get('redis');

			return new ConnectionManager($config['default'], $config['configurations']);
		});
	}
}