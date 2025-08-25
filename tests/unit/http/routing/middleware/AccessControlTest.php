<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\routing\middleware;

use mako\http\Request;
use mako\http\request\Headers as RequestHeaders;
use mako\http\Response;
use mako\http\response\Headers as ResponseHeaders;
use mako\http\routing\middleware\AccessControl;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class AccessControlTest extends TestCase
{
	/**
	 *
	 */
	public function testAllowAllWithNoOrigin(): void
	{
		$middleware = new class extends AccessControl {
			protected bool $allowAllDomains = true;
		};

		$requestHeaders = Mockery::mock(RequestHeaders::class);

		$requestHeaders->shouldReceive('get')->once()->with('Origin')->andReturn(null);

		$request = Mockery::mock(Request::class);

		(function () use ($requestHeaders): void {
			$this->headers = $requestHeaders;
		})->bindTo($request, Request::class)();

		$responseHeaders = Mockery::mock(ResponseHeaders::class);

		$responseHeaders->shouldReceive('add')->with('Access-Control-Allow-Origin', '*')->once();

		$response = Mockery::mock(Response::class);

		(function () use ($responseHeaders): void {
			$this->headers = $responseHeaders;
		})->bindTo($response, Response::class)();

		$middleware->execute($request, $response, function ($request, $response) {
			return $response;
		});
	}

	/**
	 *
	 */
	public function testAllowAllWithOriginButNoAllowedDomains(): void
	{
		$middleware = new class extends AccessControl {
			protected bool $allowAllDomains = true;
		};

		$requestHeaders = Mockery::mock(RequestHeaders::class);

		$requestHeaders->shouldReceive('get')->once()->with('Origin')->andReturn('https://example.org');

		$request = Mockery::mock(Request::class);

		(function () use ($requestHeaders): void {
			$this->headers = $requestHeaders;
		})->bindTo($request, Request::class)();

		$responseHeaders = Mockery::mock(ResponseHeaders::class);

		$responseHeaders->shouldReceive('add')->with('Access-Control-Allow-Origin', '*')->once();

		$response = Mockery::mock(Response::class);

		(function () use ($responseHeaders): void {
			$this->headers = $responseHeaders;
		})->bindTo($response, Response::class)();

		$middleware->execute($request, $response, function ($request, $response) {
			return $response;
		});
	}

	/**
	 *
	 */
	public function testAllowAllWithOriginAndAllowedDomains(): void
	{
		$middleware = new class extends AccessControl {
			protected bool $allowAllDomains = true;

			protected array $allowedDomains =
			[
				'https://example.org',
			];
		};

		$requestHeaders = Mockery::mock(RequestHeaders::class);

		$requestHeaders->shouldReceive('get')->once()->with('Origin')->andReturn('https://example.org');

		$request = Mockery::mock(Request::class);

		(function () use ($requestHeaders): void {
			$this->headers = $requestHeaders;
		})->bindTo($request, Request::class)();

		$responseHeaders = Mockery::mock(ResponseHeaders::class);

		$responseHeaders->shouldReceive('add')->with('Access-Control-Allow-Origin', 'https://example.org')->once();

		$responseHeaders->shouldReceive('add')->with('Vary', 'Origin', false)->once();

		$response = Mockery::mock(Response::class);

		(function () use ($responseHeaders): void {
			$this->headers = $responseHeaders;
		})->bindTo($response, Response::class)();

		$middleware->execute($request, $response, function ($request, $response) {
			return $response;
		});
	}

	/**
	 *
	 */
	public function testAllowWithValidDomain(): void
	{
		$middleware = new class extends AccessControl {
			protected array $allowedDomains =
			[
				'https://example.org',
			];
		};

		$requestHeaders = Mockery::mock(RequestHeaders::class);

		$requestHeaders->shouldReceive('get')->once()->with('Origin')->andReturn('https://example.org');

		$request = Mockery::mock(Request::class);

		(function () use ($requestHeaders): void {
			$this->headers = $requestHeaders;
		})->bindTo($request, Request::class)();

		$responseHeaders = Mockery::mock(ResponseHeaders::class);

		$responseHeaders->shouldReceive('add')->with('Access-Control-Allow-Origin', 'https://example.org')->once();

		$responseHeaders->shouldReceive('add')->with('Vary', 'Origin', false)->once();

		$response = Mockery::mock(Response::class);

		(function () use ($responseHeaders): void {
			$this->headers = $responseHeaders;
		})->bindTo($response, Response::class)();

		$middleware->execute($request, $response, function ($request, $response) {
			return $response;
		});
	}

	/**
	 *
	 */
	public function testAllowWithInvalidDomain(): void
	{
		$middleware = new class extends AccessControl {
			protected array $allowedDomains =
			[
				'https://example.org',
			];
		};

		$requestHeaders = Mockery::mock(RequestHeaders::class);

		$requestHeaders->shouldReceive('get')->once()->with('Origin')->andReturn('https://example.com');

		$request = Mockery::mock(Request::class);

		(function () use ($requestHeaders): void {
			$this->headers = $requestHeaders;
		})->bindTo($request, Request::class)();

		$response = Mockery::mock(Response::class);

		$middleware->execute($request, $response, function ($request, $response) {
			return $response;
		});
	}

	/**
	 *
	 */
	public function testAllowWithNoOrigin(): void
	{
		$middleware = new class extends AccessControl {
			protected array $allowedDomains =
			[
				'https://example.org',
			];
		};

		$requestHeaders = Mockery::mock(RequestHeaders::class);

		$requestHeaders->shouldReceive('get')->once()->with('Origin')->andReturn(null);

		$request = Mockery::mock(Request::class);

		(function () use ($requestHeaders): void {
			$this->headers = $requestHeaders;
		})->bindTo($request, Request::class)();

		$response = Mockery::mock(Response::class);

		$middleware->execute($request, $response, function ($request, $response) {
			return $response;
		});
	}

	/**
	 *
	 */
	public function testAllowsCredentials(): void
	{
		$middleware = new class extends AccessControl {
			protected bool $allowAllDomains = true;
			protected bool $allowCredentials = true;
		};

		$requestHeaders = Mockery::mock(RequestHeaders::class);

		$requestHeaders->shouldReceive('get')->once()->with('Origin')->andReturn(null);

		$request = Mockery::mock(Request::class);

		(function () use ($requestHeaders): void {
			$this->headers = $requestHeaders;
		})->bindTo($request, Request::class)();

		$responseHeaders = Mockery::mock(ResponseHeaders::class);

		$responseHeaders->shouldReceive('add')->with('Access-Control-Allow-Origin', '*')->once();

		$responseHeaders->shouldReceive('add')->with('Access-Control-Allow-Credentials', 'true')->once();

		$response = Mockery::mock(Response::class);

		(function () use ($responseHeaders): void {
			$this->headers = $responseHeaders;
		})->bindTo($response, Response::class)();

		$middleware->execute($request, $response, function ($request, $response) {
			return $response;
		});
	}

	/**
	 *
	 */
	public function testAllowHeaders(): void
	{
		$middleware = new class extends AccessControl {
			protected bool $allowAllDomains = true;
			protected array $allowedHeaders = ['X-Custom-Header1', 'X-Custom-Header2'];
		};

		$requestHeaders = Mockery::mock(RequestHeaders::class);

		$requestHeaders->shouldReceive('get')->once()->with('Origin')->andReturn(null);

		$request = Mockery::mock(Request::class);

		(function () use ($requestHeaders): void {
			$this->headers = $requestHeaders;
		})->bindTo($request, Request::class)();

		$responseHeaders = Mockery::mock(ResponseHeaders::class);

		$responseHeaders->shouldReceive('add')->with('Access-Control-Allow-Origin', '*')->once();

		$responseHeaders->shouldReceive('add')->with('Access-Control-Allow-Headers', 'X-Custom-Header1, X-Custom-Header2')->once();

		$response = Mockery::mock(Response::class);

		(function () use ($responseHeaders): void {
			$this->headers = $responseHeaders;
		})->bindTo($response, Response::class)();

		$middleware->execute($request, $response, function ($request, $response) {
			return $response;
		});
	}

	/**
	 *
	 */
	public function testAllowMethods(): void
	{
		$middleware = new class extends AccessControl {
			protected bool $allowAllDomains = true;
			protected array $allowedMethods = ['GET', 'POST'];
		};

		$requestHeaders = Mockery::mock(RequestHeaders::class);

		$requestHeaders->shouldReceive('get')->once()->with('Origin')->andReturn(null);

		$request = Mockery::mock(Request::class);

		(function () use ($requestHeaders): void {
			$this->headers = $requestHeaders;
		})->bindTo($request, Request::class)();

		$responseHeaders = Mockery::mock(ResponseHeaders::class);

		$responseHeaders->shouldReceive('add')->with('Access-Control-Allow-Origin', '*')->once();

		$responseHeaders->shouldReceive('add')->with('Access-Control-Allow-Methods', 'GET, POST')->once();

		$response = Mockery::mock(Response::class);

		(function () use ($responseHeaders): void {
			$this->headers = $responseHeaders;
		})->bindTo($response, Response::class)();

		$middleware->execute($request, $response, function ($request, $response) {
			return $response;
		});
	}
}
