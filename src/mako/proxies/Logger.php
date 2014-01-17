<?php

namespace mako\proxies;

use \mako\core\Application;

/**
 * Logger builder proxy.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Logger extends \mako\proxies\Proxy
{
	/**
	 * Returns instance of the class we're proxying.
	 * 
	 * @access  protected
	 * @return  Psr\Log\LoggerInterface
	 */

	protected static function instance()
	{
		return Application::instance()->get('logger');
	}
}

/** -------------------- End of file -------------------- **/