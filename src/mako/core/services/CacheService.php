<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\core\services;

use \mako\cache\CacheManager;

/**
 * Cache service.
 *
 * @author  Frederic G. Østby
 */

class CacheService extends \mako\core\services\Service
{
	/**
	 * Registers the service.
	 * 
	 * @access  public
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