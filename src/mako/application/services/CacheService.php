<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\application\services\Service;
use mako\cache\CacheManager;

/**
 * Cache service.
 *
 * @author  Frederic G. Østby
 */

class CacheService extends Service
{
	/**
	 * {@inheritdoc}
	 */

	public function register()
	{
		$this->container->registerSingleton(['mako\cache\CacheManager', 'cache'], function($container)
		{
			$config = $container->get('config')->get('cache');

			return new CacheManager($config['default'], $config['configurations'], $container);
		});
	}
}