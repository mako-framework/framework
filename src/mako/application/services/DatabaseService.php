<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\application\services\Service;
use mako\database\ConnectionManager;
use mako\database\query\Query;

/**
 * Database service.
 *
 * @author Frederic G. Østby
 */
class DatabaseService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->container->registerSingleton([ConnectionManager::class, 'database'], function($container)
		{
			if($container->has('pagination'))
			{
				Query::setPaginationFactory(function() use ($container)
				{
					return $container->get('pagination');
				});
			}

			$config = $container->get('config')->get('database');

			return new ConnectionManager($config['default'], $config['configurations']);
		});
	}
}
