<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\proxies;

use \mako\core\Application;

/**
 * Session proxy.
 *
 * @author  Frederic G. Østby
 */

class Session extends \mako\proxies\Proxy
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
	 * @return  \mako\session\Session
	 */

	protected static function instance()
	{
		return Application::instance()->get('session');
	}
}