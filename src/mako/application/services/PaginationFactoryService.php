<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\application\services\Service;
use mako\pagination\PaginationFactory;
use mako\pagination\PaginationFactoryInterface;

/**
 * Pagination factory service.
 *
 * @author  Frederic G. Østby
 */

class PaginationFactoryService extends Service
{
	/**
	 * {@inheritdoc}
	 */

	public function register()
	{
		$this->container->registerSingleton([PaginationFactoryInterface::class, 'pagination'], function($container)
		{
			$paginationFactory = new PaginationFactory($container->get('request'), $container->get('config')->get('pagination'));

			if($container->has('urlBuilder'))
			{
				$paginationFactory->setURLBuilder($container->get('urlBuilder'));
			}

			if($container->has('view'))
			{
				$paginationFactory->setViewFactory($container->get('view'));
			}

			return $paginationFactory;
		});
	}
}