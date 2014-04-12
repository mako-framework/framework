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
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class methods
	//---------------------------------------------
	
	/**
	 * Registers the service.
	 * 
	 * @access  public
	 */

	public function register()
	{
		$this->container->registerSingleton(['mako\view\ViewFactory', 'viewfactory'], function($container)
		{
			$app = $container->get('app');

			return new ViewFactory($app->getApplicationPath(), $app->getCharset());
		});
	}
}