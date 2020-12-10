<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\http\Request;
use mako\http\Response;
use mako\http\routing\Dispatcher;
use mako\http\routing\Router;
use mako\http\routing\Routes;
use mako\http\routing\URLBuilder;
use mako\security\Signer;

/**
 * HTTP service.
 */
class HTTPService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		$app = $this->app;

		$config = $this->config->get('application');

		// Request

		$this->container->registerSingleton([Request::class, 'request'], static function($container) use ($config)
		{
			$request = new Request(['languages' => $config['languages']], $container->get(Signer::class));

			if(!empty($config['trusted_proxies']))
			{
				$request->setTrustedProxies($config['trusted_proxies']);
			}

			return $request;
		});

		// Response

		$this->container->registerSingleton([Response::class, 'response'], static function($container) use ($app)
		{
			return new Response($container->get(Request::class), $app->getCharset(), $container->get(Signer::class));
		});

		// Routes

		$this->container->registerSingleton([Routes::class, 'routes'], static function($container) use ($app)
		{
			$routes = new Routes;

			(function($app, $container, $routes): void
			{
				include "{$app->getPath()}/routing/routes.php";
			})
			->bindTo($app)($app, $container, $routes);

			return $routes;
		});

		// Router

		$this->container->registerSingleton(Router::class, static function($container) use ($app)
		{
			$router = new Router($container->get(Routes::class), $container);

			(function($app, $container, $router): void
			{
				include "{$app->getPath()}/routing/constraints.php";
			})
			->bindTo($app)($app, $container, $router);

			return $router;
		});

		// Dispatcher

		$this->container->registerSingleton(Dispatcher::class, static function($container) use ($app)
		{
			$dispatcher = new Dispatcher($container->get(Request::class), $container->get(Response::class), $container);

			(function($app, $container, $dispatcher): void
			{
				include "{$app->getPath()}/routing/middleware.php";
			})
			->bindTo($app)($app, $container, $dispatcher);

			return $dispatcher;
		});

		// URLBuilder

		$this->container->registerSingleton([URLBuilder::class, 'urlBuilder'], static function($container) use ($config)
		{
			return new URLBuilder($container->get(Request::class), $container->get(Routes::class), $config['clean_urls'], $config['base_url']);
		});
	}
}
