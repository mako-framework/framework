<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\services;

use \mako\http\Request;

/**
 * Request service.
 *
 * @author  Frederic G. Østby
 */

class RequestService extends \mako\application\services\Service
{
	/**
	 * Registers the service.
	 * 
	 * @access  public
	 */

	public function register()
	{
		$this->container->registerSingleton(['mako\http\Request', 'request'], function($container)
		{
			$config = $container->get('config');

			$request = new Request(['languages' => $config->get('application.languages')], $container->get('signer'));

			$request->setTrustedProxies($config->get('application.trusted_proxies'));

			return $request;
		});
	}
}