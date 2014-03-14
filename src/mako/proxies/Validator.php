<?php

namespace mako\proxies;

use \mako\core\Application;

/**
 * Validator factory proxy.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Validator extends \mako\proxies\Proxy
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
	 * Returns instance of the class we're proxying.
	 * 
	 * @access  protected
	 * @return  \mako\validator\ValidatorFactory
	 */

	protected static function instance()
	{
		return Application::instance()->get('validatorfactory');
	}
}

/** -------------------- End of file -------------------- **/