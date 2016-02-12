<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\application\services\Service;
use mako\http\Request;
use mako\http\Response;
use mako\http\routing\URLBuilder;
use mako\http\routing\Routes;

/**
 * HTTP service.
 *
 * @author  Frederic G. Østby
 */

class HTTPService extends Service
{
	/**
	 * {@inheritdoc}
	 */

	public function register()
	{
		// Request

		$this->container->registerSingleton([Request::class, 'request'], function($container)
		{
			$config = $container->get('config');

			$request = new Request(['languages' => $config->get('application.languages')], $container->get('signer'));

			$request->setTrustedProxies($config->get('application.trusted_proxies'));

			return $request;
		});

		// Response

		$this->container->registerSingleton([Response::class, 'response'], function($container)
		{
			return new Response($container->get('request'), $container->get('app')->getCharset(), $container->get('signer'));
		});

		// Routes

		$this->container->registerSingleton([Routes::class, 'routes'], Routes::class);

		// URLBuilder

		$this->container->registerSingleton([URLBuilder::class, 'urlBuilder'], function($container)
		{
			return new URLBuilder($container->get('request'), $container->get('routes'), $container->get('config')->get('application.clean_urls'));
		});
	}
}