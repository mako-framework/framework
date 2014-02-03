<?php

namespace mako\core\services;

use \mako\utility\Humanizer;

/**
 * Humanizer service.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class HumanizerService extends \mako\core\services\Service
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
		$this->application->registerSingleton(['mako\utility\Humanizer', 'humanizer'], function($app)
		{
			return new Humanizer($app->get('i18n'));
		});
	}
}

/** -------------------- End of file -------------------- **/