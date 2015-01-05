<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\application\services\Service;
use mako\http\Request;

/**
 * Request service.
 *
 * @author  Frederic G. Østby
 */

class RequestService extends Service
{
	/**
	 * {@inheritdoc}
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