<?php

namespace mako\core\services;

use \mako\http\routing\URLBuilder;

/**
 * URL builder service.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
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
		$this->application->registerSingleton(['mako\http\routing\URLBuilder', 'urlbuilder'], function()
		{
			return new URLBuilder($this->get('request'), $this->get('routes'), $this->application->getConfig()->get('application.clean_urls'));
		});
	}
}

/** -------------------- End of file -------------------- **/