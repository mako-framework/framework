<?php

namespace mako\proxies;

use \mako\core\Application;

/**
 * Config proxy.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Config extends \mako\proxies\Proxy
{
	/**
	 * Returns instance of the class we're proxying.
	 * 
	 * @access  protected
	 * @return  \mako\core\Config
	 */

	protected static function instance()
	{
		return Application::instance()->get('config');
	}
}

/** -------------------- End of file -------------------- **/