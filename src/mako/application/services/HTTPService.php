<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\application\services\Service;
use mako\http\Request;
use mako\http\Response;
use mako\http\routing\Middleware;
use mako\http\routing\URLBuilder;
use mako\http\routing\Routes;

/**
 * HTTP service.
 *
 * @author Frederic G. Østby
 */
class HTTPService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$app = $this->container->get('app');

		$config = $this->container->get('config')->get('application');

		// Request

		$this->container->registerSingleton([Request::class, 'request'], function($container) use ($config)
		{
			$request = new Request(['languages' => $config['languages']], $container->get('signer'));

			if(!empty($config['trusted_proxies']))
			{
				$request->setTrustedProxies($config['trusted_proxies']);
			}

			return $request;
		});

		// Response

		$this->container->registerSingleton([Response::class, 'response'], function($container) use ($app)
		{
			return new Response($container->get('request'), $app->getCharset(), $container->get('signer'));
		});

		// Middleware

		$this->container->registerSingleton(Middleware::class, function($container) use ($app)
		{
			$middleware = new Middleware;

			(function($app, $container, $middleware)
			{
				include $app->getPath() . '/routing/middleware.php';
			})
			->bindTo($app)($app, $container, $middleware);

			return $middleware;
		});

		// Routes

		$this->container->registerSingleton([Routes::class, 'routes'], function($container) use ($app)
		{
			$routes = new Routes;

			(function($app, $container, $routes)
			{
				include $app->getPath() . '/routing/routes.php';
			})
			->bindTo($app)($app, $container, $routes);

			return $routes;
		});

		// URLBuilder

		$this->container->registerSingleton([URLBuilder::class, 'urlBuilder'], function($container) use ($config)
		{
			return new URLBuilder($container->get('request'), $container->get('routes'), $config['clean_urls'], $config['base_url']);
		});
	}
}
