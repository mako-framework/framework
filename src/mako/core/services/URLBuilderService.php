<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\core\services;

use \mako\http\routing\URLBuilder;

/**
 * URL builder service.
 *
 * @author  Frederic G. Østby
 */

class URLBuilderService extends \mako\core\services\Service
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
		$this->application->registerSingleton(['mako\http\routing\URLBuilder', 'urlbuilder'], function($app)
		{
			return new URLBuilder($app->get('request'), $app->get('routes'), $app->getConfig()->get('application.clean_urls'));
		});
	}
}

