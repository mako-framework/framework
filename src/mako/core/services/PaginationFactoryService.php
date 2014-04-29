<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\core\services;

use \mako\pagination\PaginationFactory;

/**
 * Pagination factory service.
 *
 * @author  Frederic G. Østby
 */

class PaginationFactoryService extends \mako\core\services\Service
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
		$this->container->registerSingleton(['mako\pagination\PaginationFactory', 'pagination'], function($container)
		{
			$paginationFactory = new PaginationFactory($container->get('request'), $container->get('config')->get('pagination'));

			if($container->has('urlbuilder'))
			{
				$paginationFactory->setURLBuilder($container->get('urlbuilder'));
			}

			if($container->has('view'))
			{
				$paginationFactory->setViewFactory($container->get('view'));
			}

			return $paginationFactory;
		});
	}
}