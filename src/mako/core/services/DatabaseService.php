<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\core\services;

use \mako\database\ConnectionManager;

/**
 * Database service.
 *
 * @author  Frederic G. Østby
 */

class DatabaseService extends \mako\core\services\Service
{
	/**
	 * Registers the service.
	 * 
	 * @access  public
	 */

	public function register()
	{
		$this->container->registerSingleton(['mako\database\ConnectionManager', 'database'], function($container)
		{
			$config = $container->get('config')->get('database');

			return new ConnectionManager($config['default'], $config['configurations']);
		});
	}
}