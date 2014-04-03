<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\proxies;

use \mako\core\Application;

/**
 * URL builder proxy.
 *
 * @author  Frederic G. Østby
 */

class URL extends \mako\proxies\Proxy
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
	 * @return  \mako\http\routing\URLBuilder
	 */

	protected static function instance()
	{
		return Application::instance()->get('urlbuilder');
	}
}