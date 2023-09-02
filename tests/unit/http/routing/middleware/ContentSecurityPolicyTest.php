<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\routing\middleware;

use mako\http\Request;
use mako\http\Response;
use mako\http\response\Headers;
use mako\http\routing\middleware\ContentSecurityPolicy;
use mako\syringe\Container;
use mako\tests\TestCase;
use mako\view\ViewFactory;
use Mockery;

/**
 * @group unit
 */
class ContentSecurityPolicyTest extends TestCase
{
	/**
	 *
	 */
	public function testWithDefaultConfig(): void
	{
		/** @var \mako\syringe\Container|\Mockery\MockInterface $container */
		$container = Mockery::mock(Container::class);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		/** @var \mako\http\response\Headers|\Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('add')->once()->with('Content-Security-Policy', "base-uri 'self'; default-src 'self'; object-src 'none'");

		$response->shouldReceive('getHeaders')->once()->andReturn($headers);

		$next = function ($request, $response)
		{
			$this->assertInstanceOf(Request::class, $request);

			$this->assertInstanceOf(Response::class, $response);

			return $response;
		};

		$contentSecurityPolicy = new ContentSecurityPolicy($container);

		$contentSecurityPolicy->execute($request, $response, $next);
	}

	/**
	 *
	 */
	public function testWithReportOnly(): void
	{
		/** @var \mako\syringe\Container|\Mockery\MockInterface $container */
		$container = Mockery::mock(Container::class);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		/** @var \mako\http\response\Headers|\Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('add')->once()->with('Content-Security-Policy-Report-Only', "base-uri 'self'; default-src 'self'; object-src 'none'");

		$response->shouldReceive('getHeaders')->once()->andReturn($headers);

		$next = function ($request, $response)
		{
			$this->assertInstanceOf(Request::class, $request);

			$this->assertInstanceOf(Response::class, $response);

			return $response;
		};

		$contentSecurityPolicy = new class ($container) extends ContentSecurityPolicy
		{
			protected bool $reportOnly = true;
		};

		$contentSecurityPolicy->execute($request, $response, $next);
	}

	/**
	 *
	 */
	public function testWithCustomCsp(): void
	{
		/** @var \mako\syringe\Container|\Mockery\MockInterface $container */
		$container = Mockery::mock(Container::class);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		/** @var \mako\http\response\Headers|\Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('add')->once()->with('Content-Security-Policy', "block-all-mixed-content; default-src 'self'; script-src 'unsafe-inline' 'unsafe-eval' 'none'");

		$response->shouldReceive('getHeaders')->once()->andReturn($headers);

		$next = function ($request, $response)
		{
			$this->assertInstanceOf(Request::class, $request);

			$this->assertInstanceOf(Response::class, $response);

			return $response;
		};

		$contentSecurityPolicy = new class ($container) extends ContentSecurityPolicy
		{
			protected array $directives =
			[
				'block-all-mixed-content' => true,
				'default-src'             => ['self'],
				'script-src'              => ['unsafe-inline', 'unsafe-eval', 'none'],
			];
		};

		$contentSecurityPolicy->execute($request, $response, $next);
	}

	/**
	 *
	 */
	public function testWithCustomCspWithNonce(): void
	{
		/** @var \mako\view\ViewFactory|\Mockery\MockInterface $viewFactory */
		$viewFactory = Mockery::mock(ViewFactory::class);

		$viewFactory->shouldReceive('assign')->once()->with('_csp_nonce_', 'foobar');

		/** @var \mako\syringe\Container|\Mockery\MockInterface $container */
		$container = Mockery::mock(Container::class);

		$container->shouldReceive('has')->once()->with(ViewFactory::class)->andReturn(true);

		$container->shouldReceive('get')->once()->with(ViewFactory::class)->andReturn($viewFactory);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		/** @var \mako\http\response\Headers|\Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('add')->once()->with('Content-Security-Policy', "default-src 'nonce-foobar'");

		$response->shouldReceive('getHeaders')->once()->andReturn($headers);

		$next = function ($request, $response)
		{
			$this->assertInstanceOf(Request::class, $request);

			$this->assertInstanceOf(Response::class, $response);

			return $response;
		};

		$contentSecurityPolicy = new class ($container) extends ContentSecurityPolicy
		{
			protected array $directives =
			[
				'default-src' => ['nonce'],
			];

			protected function generateNonce(): string
			{
				return 'foobar';
			}
		};

		$contentSecurityPolicy->execute($request, $response, $next);
	}

	/**
	 *
	 */
	public function testWithCustomCspWithNonceAndCustomNonceVariableName(): void
	{
		/** @var \mako\view\ViewFactory|\Mockery\MockInterface $viewFactory */
		$viewFactory = Mockery::mock(ViewFactory::class);

		$viewFactory->shouldReceive('assign')->once()->with('cspNonce', 'foobar');

		/** @var \mako\syringe\Container|\Mockery\MockInterface $container */
		$container = Mockery::mock(Container::class);

		$container->shouldReceive('has')->once()->with(ViewFactory::class)->andReturn(true);

		$container->shouldReceive('get')->once()->with(ViewFactory::class)->andReturn($viewFactory);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		/** @var \mako\http\response\Headers|\Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('add')->once()->with('Content-Security-Policy', "default-src 'nonce-foobar'");

		$response->shouldReceive('getHeaders')->once()->andReturn($headers);

		$next = function ($request, $response)
		{
			$this->assertInstanceOf(Request::class, $request);

			$this->assertInstanceOf(Response::class, $response);

			return $response;
		};

		$contentSecurityPolicy = new class ($container) extends ContentSecurityPolicy
		{
			protected string $nonceVariableName = 'cspNonce';

			protected array $directives =
			[
				'default-src' => ['nonce'],
			];

			protected function generateNonce(): string
			{
				return 'foobar';
			}
		};

		$contentSecurityPolicy->execute($request, $response, $next);
	}

	/**
	 *
	 */
	public function testWithCustomCspWithReportTo(): void
	{
		/** @var \mako\syringe\Container|\Mockery\MockInterface $container */
		$container = Mockery::mock(Container::class);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		/** @var \mako\http\response\Headers|\Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('add')->once()->with('Report-To', '{"group":"csp-endpoint","max-age":10886400,"endpoints":[{"url":"https:\/\/example.com\/csp-reports"}]}');

		$headers->shouldReceive('add')->once()->with('Content-Security-Policy', 'report-to csp-endpoint');

		$response->shouldReceive('getHeaders')->once()->andReturn($headers);

		$next = function ($request, $response)
		{
			$this->assertInstanceOf(Request::class, $request);

			$this->assertInstanceOf(Response::class, $response);

			return $response;
		};

		$contentSecurityPolicy = new class ($container) extends ContentSecurityPolicy
		{
			protected array $reportTo =
			[
				[
					'group'     => 'csp-endpoint',
					'max-age'   => 10886400,
					'endpoints' =>
					[
						['url' => 'https://example.com/csp-reports'],
					],
				],
			];

			protected array $directives =
			[

				'report-to' => ['csp-endpoint'],
			];
		};

		$contentSecurityPolicy->execute($request, $response, $next);
	}
}
