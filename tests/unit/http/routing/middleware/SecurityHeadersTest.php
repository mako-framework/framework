<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\routing\middleware;

use mako\http\Request;
use mako\http\Response;
use mako\http\response\Headers;
use mako\http\routing\middleware\SecurityHeaders;
use mako\syringe\Container;
use mako\tests\TestCase;
use mako\view\ViewFactory;
use Mockery;

/**
 * @group unit
 */
class SecurityHeadersTest extends TestCase
{
	/**
	 *
	 */
	public function testWithDefaultConfig(): void
	{
		$container = Mockery::mock(Container::class);

		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class);

		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('add')->once()->with('X-Content-Type-Options', 'nosniff');

		$headers->shouldReceive('add')->once()->with('X-Frame-Options', 'sameorigin');

		$headers->shouldReceive('add')->once()->with('X-XSS-Protection', '1; mode=block');

		$headers->shouldReceive('add')->once()->with('Content-Security-Policy', "base-uri 'self'; default-src 'self'; object-src 'none'");

		$response->shouldReceive('getHeaders')->once()->andReturn($headers);

		$next = function($request, $response)
		{
			$this->assertInstanceOf(Request::class, $request);

			$this->assertInstanceOf(Response::class, $response);

			return $response;
		};

		$securityHeaders = new SecurityHeaders($container);

		$securityHeaders->execute($request, $response, $next);
	}

	/**
	 *
	 */
	public function testWithReportOnly(): void
	{
		$container = Mockery::mock(Container::class);

		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class);

		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('add')->once()->with('X-Content-Type-Options', 'nosniff');

		$headers->shouldReceive('add')->once()->with('X-Frame-Options', 'sameorigin');

		$headers->shouldReceive('add')->once()->with('X-XSS-Protection', '1; mode=block');

		$headers->shouldReceive('add')->once()->with('Content-Security-Policy-Report-Only', "base-uri 'self'; default-src 'self'; object-src 'none'");

		$response->shouldReceive('getHeaders')->once()->andReturn($headers);

		$next = function($request, $response)
		{
			$this->assertInstanceOf(Request::class, $request);

			$this->assertInstanceOf(Response::class, $response);

			return $response;
		};

		$securityHeaders = new class ($container) extends SecurityHeaders
		{
			protected $cspReportOnly = true;
		};

		$securityHeaders->execute($request, $response, $next);
	}

	/**
	 *
	 */
	public function testWithCustomHeadersAndNoCsp(): void
	{
		$container = Mockery::mock(Container::class);

		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class);

		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('add')->once()->with('X-Foo', 'bar');

		$response->shouldReceive('getHeaders')->once()->andReturn($headers);

		$next = function($request, $response)
		{
			$this->assertInstanceOf(Request::class, $request);

			$this->assertInstanceOf(Response::class, $response);

			return $response;
		};

		$securityHeaders = new class ($container) extends SecurityHeaders
		{
			protected $headers =
			[
				'X-Foo' => 'bar',
			];

			protected $cspDirectives = null;
		};

		$securityHeaders->execute($request, $response, $next);
	}

	/**
	 *
	 */
	public function testWithCustomCspAndNoHeaders(): void
	{
		$container = Mockery::mock(Container::class);

		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class);

		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('add')->once()->with('Content-Security-Policy', "block-all-mixed-content; default-src 'self'; script-src 'unsafe-inline' 'unsafe-eval' 'none'");

		$response->shouldReceive('getHeaders')->once()->andReturn($headers);

		$next = function($request, $response)
		{
			$this->assertInstanceOf(Request::class, $request);

			$this->assertInstanceOf(Response::class, $response);

			return $response;
		};

		$securityHeaders = new class ($container) extends SecurityHeaders
		{
			protected $headers = null;

			protected $cspDirectives =
			[
				'block-all-mixed-content' => true,
				'default-src'             => ['self'],
				'script-src'              => ['unsafe-inline', 'unsafe-eval', 'none'],
			];
		};

		$securityHeaders->execute($request, $response, $next);
	}

	/**
	 *
	 */
	public function testWithCustomCspWithNonceAndNoHeaders(): void
	{
		$viewFactory = Mockery::mock(ViewFactory::class);

		$viewFactory->shouldReceive('assign')->once()->with('_csp_nonce_', 'foobar');

		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->once()->with(ViewFactory::class)->andReturn($viewFactory);

		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class);

		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('add')->once()->with('Content-Security-Policy', "default-src 'nonce-foobar'");

		$response->shouldReceive('getHeaders')->once()->andReturn($headers);

		$next = function($request, $response)
		{
			$this->assertInstanceOf(Request::class, $request);

			$this->assertInstanceOf(Response::class, $response);

			return $response;
		};

		$securityHeaders = new class ($container) extends SecurityHeaders
		{
			protected $headers = null;

			protected $cspDirectives =
			[
				'default-src' => ['nonce'],
			];

			protected function generateCspNonce(): string
			{
				return 'foobar';
			}
		};

		$securityHeaders->execute($request, $response, $next);
	}

	/**
	 *
	 */
	public function testWithCustomCspWithNonceAndCustomNonceVariableNameAndNoHeaders(): void
	{
		$viewFactory = Mockery::mock(ViewFactory::class);

		$viewFactory->shouldReceive('assign')->once()->with('cspNonce', 'foobar');

		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->once()->with(ViewFactory::class)->andReturn($viewFactory);

		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class);

		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('add')->once()->with('Content-Security-Policy', "default-src 'nonce-foobar'");

		$response->shouldReceive('getHeaders')->once()->andReturn($headers);

		$next = function($request, $response)
		{
			$this->assertInstanceOf(Request::class, $request);

			$this->assertInstanceOf(Response::class, $response);

			return $response;
		};

		$securityHeaders = new class ($container) extends SecurityHeaders
		{
			protected $headers = null;

			protected $cspNonceVariableName = 'cspNonce';

			protected $cspDirectives =
			[
				'default-src' => ['nonce'],
			];

			protected function generateCspNonce(): string
			{
				return 'foobar';
			}
		};

		$securityHeaders->execute($request, $response, $next);
	}

	/**
	 *
	 */
	public function testWithCustomCspWithReportToAndNoHeaders(): void
	{
		$container = Mockery::mock(Container::class);

		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class);

		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('add')->once()->with('Report-To', '{"group":"csp-endpoint","max-age":10886400,"endpoints":[{"url":"https:\/\/example.com\/csp-reports"}]}');

		$headers->shouldReceive('add')->once()->with('Content-Security-Policy', 'report-to csp-endpoint');

		$response->shouldReceive('getHeaders')->once()->andReturn($headers);

		$next = function($request, $response)
		{
			$this->assertInstanceOf(Request::class, $request);

			$this->assertInstanceOf(Response::class, $response);

			return $response;
		};

		$securityHeaders = new class ($container) extends SecurityHeaders
		{
			protected $headers = null;

			protected $reportTo =
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

			protected $cspDirectives =
			[

				'report-to' => ['csp-endpoint'],
			];
		};

		$securityHeaders->execute($request, $response, $next);
	}
}
