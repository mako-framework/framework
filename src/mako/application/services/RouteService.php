<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\services;

/**
 * Route service.
 *
 * @author  Frederic G. Østby
 */

class RouteService extends \mako\application\services\Service
{
	/**
	 * Registers the service.
	 * 
	 * @access  public
	 */

	public function register()
	{
		$this->container->registerSingleton(['mako\http\routing\Routes', 'routes'], 'mako\http\routing\Routes');
	}
}