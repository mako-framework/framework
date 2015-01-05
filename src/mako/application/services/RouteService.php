<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\application\services\Service;

/**
 * Route service.
 *
 * @author  Frederic G. Østby
 */

class RouteService extends Service
{
	/**
	 * {@inheritdoc}
	 */

	public function register()
	{
		$this->container->registerSingleton(['mako\http\routing\Routes', 'routes'], 'mako\http\routing\Routes');
	}
}