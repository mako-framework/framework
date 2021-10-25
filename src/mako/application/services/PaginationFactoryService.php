<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\http\Request;
use mako\http\routing\URLBuilder;
use mako\pagination\PaginationFactory;
use mako\pagination\PaginationFactoryInterface;
use mako\view\ViewFactory;

/**
 * Pagination factory service.
 *
 * @author Frederic G. Østby
 */
class PaginationFactoryService extends Service
{
	/**
	 * {@inheritDoc}
	 */
	public function register(): void
	{
		$this->container->registerSingleton([PaginationFactoryInterface::class, 'pagination'], function($container)
		{
			$paginationFactory = new PaginationFactory($container->get(Request::class), $this->config->get('pagination'));

			if($container->has(URLBuilder::class))
			{
				$paginationFactory->setURLBuilder($container->get(URLBuilder::class));
			}

			if($container->has(ViewFactory::class))
			{
				$paginationFactory->setViewFactory($container->get(ViewFactory::class));
			}

			return $paginationFactory;
		});
	}
}
