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
use Override;

/**
 * HTTP service.
 */
class HTTPService extends Service
{
	/**
	 * Returns path to the application routing.
	 */
	protected function getRoutingPath(): string
	{
		return "{$this->app->getPath()}/http/routing";
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function register(): void
	{
		$app = $this->app;

		$config = $this->config->get('application');

		$routingPath = $this->getRoutingPath();

		// Request

		$this->container->registerSingleton([Request::class, 'request'], static function ($container) use ($config) {
			$request = new Request(['languages' => $config['languages']], $container->get(Signer::class), $config['script_name'] ?? null);

			if (!empty($config['trusted_proxies'])) {
				$request->setTrustedProxies($config['trusted_proxies']);
			}

			return $request;
		});

		// Response

		$this->container->registerSingleton([Response::class, 'response'], static fn ($container) => new Response($container->get(Request::class), $app->getCharset(), $container->get(Signer::class)));

		// Routes

		$this->container->registerSingleton([Routes::class, 'routes'], static function ($container) use ($app, $routingPath) {
			$routes = new Routes;

			(function ($app, $container, $routes) use ($routingPath): void {
				include "{$routingPath}/routes.php";
			})
			->bindTo($app)($app, $container, $routes);

			return $routes;
		});

		// Router

		$this->container->registerSingleton(Router::class, static function ($container) use ($app, $routingPath) {
			$router = new Router($container->get(Routes::class), $container);

			(function ($app, $container, $router) use ($routingPath): void {
				include "{$routingPath}/constraints.php";
			})
			->bindTo($app)($app, $container, $router);

			return $router;
		});

		// Dispatcher

		$this->container->registerSingleton(Dispatcher::class, static function ($container) use ($app, $routingPath) {
			$dispatcher = new Dispatcher($container->get(Request::class), $container->get(Response::class), $container);

			(function ($app, $container, $dispatcher) use ($routingPath): void {
				include "{$routingPath}/middleware.php";
			})
			->bindTo($app)($app, $container, $dispatcher);

			return $dispatcher;
		});

		// URLBuilder

		$this->container->registerSingleton([URLBuilder::class, 'urlBuilder'], static fn ($container) => new URLBuilder($container->get(Request::class), $container->get(Routes::class), $config['clean_urls'], $config['base_url']));
	}
}
