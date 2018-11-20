<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\database\ConnectionManager;
use mako\database\query\Query;
use mako\pagination\PaginationFactoryInterface;

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
			if($container->has(PaginationFactoryInterface::class))
			{
				Query::setPaginationFactory(function() use ($container)
				{
					return $container->get(PaginationFactoryInterface::class);
				});
			}

			$config = $this->config->get('database');

			return new ConnectionManager($config['default'], $config['configurations']);
		});
	}
}
