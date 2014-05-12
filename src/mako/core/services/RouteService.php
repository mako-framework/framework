<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\core\services;

/**
 * Route service.
 *
 * @author  Frederic G. Østby
 */

class RouteService extends \mako\core\services\Service
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