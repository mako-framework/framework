<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\core\services;

use \mako\view\ViewFactory;

/**
 * View factory service.
 *
 * @author  Frederic G. Østby
 */

class ViewFactoryService extends \mako\core\services\Service
{
	/**
	 * Registers the service.
	 * 
	 * @access  public
	 */

	public function register()
	{
		$this->container->registerSingleton(['mako\view\ViewFactory', 'view'], function($container)
		{
			$app = $container->get('app');

			return new ViewFactory($app->getApplicationPath(), $app->getCharset());
		});
	}
}