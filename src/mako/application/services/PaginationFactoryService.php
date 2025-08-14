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
use Override;

/**
 * Pagination factory service.
 */
class PaginationFactoryService extends Service
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function register(): void
	{
		$config = $this->config;

		// Register the pagination factory

		$this->container->registerSingleton([PaginationFactoryInterface::class, 'pagination'], static function ($container) use ($config) {
			$paginationFactory = new PaginationFactory($container->get(Request::class), $config->get('pagination'));

			if ($container->has(URLBuilder::class)) {
				$paginationFactory->setURLBuilder($container->get(URLBuilder::class));
			}

			if ($container->has(ViewFactory::class)) {
				$paginationFactory->setViewFactory($container->get(ViewFactory::class));
			}

			return $paginationFactory;
		});
	}
}
