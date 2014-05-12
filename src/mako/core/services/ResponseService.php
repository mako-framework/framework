<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\core\services;

use \mako\http\Response;

/**
 * Response service.
 *
 * @author  Frederic G. Østby
 */

class ResponseService extends \mako\core\services\Service
{
	/**
	 * Registers the service.
	 * 
	 * @access  public
	 */

	public function register()
	{
		$this->container->registerSingleton(['mako\http\Response', 'response'], function($container)
		{
			return new Response($container->get('request'), $container->get('app')->getCharset(), $container->get('signer'));
		});
	}
}