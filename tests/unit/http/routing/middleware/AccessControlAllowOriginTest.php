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
use mako\http\routing\middleware\AccessControlAllowOrigin;
use mako\tests\TestCase;
use Mockery;

/**
 * @group unit
 */
class AccessControlAllowOriginTest extends TestCase
{
	/**
	 *
	 */
	public function testAllowAll(): void
	{
		$middleware = new class extends AccessControlAllowOrigin
		{
			protected $allowAll = true;
		};

		$request         = Mockery::mock(Request::class);
		$response        = Mockery::mock(Response::class);
		$responseHeaders = Mockery::mock(ResponseHeaders::class);

		$response->shouldReceive('getHeaders')->once()->andReturn($responseHeaders);

		$responseHeaders->shouldReceive('add')->with('Access-Control-Allow-Origin', '*')->once();

		$middleware->execute($request, $response, function($request, $response)
		{
			return $response;
		});
	}

	/**
	 *
	 */
	public function testAllowWithValidDomain(): void
	{
		$middleware = new class extends AccessControlAllowOrigin
		{
			protected $allowedDomains =
			[
				'https://example.org',
			];
		};

		$request         = Mockery::mock(Request::class);
		$requestHeaders  = Mockery::mock(RequestHeaders::class);
		$response        = Mockery::mock(Response::class);
		$responseHeaders = Mockery::mock(ResponseHeaders::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($requestHeaders);

		$requestHeaders->shouldReceive('get')->once()->with('Origin')->andReturn('https://example.org');

		$response->shouldReceive('getHeaders')->times(2)->andReturn($responseHeaders);

		$responseHeaders->shouldReceive('add')->with('Access-Control-Allow-Origin', 'https://example.org')->once();

		$responseHeaders->shouldReceive('add')->with('Vary', 'Origin')->once();

		$middleware->execute($request, $response, function($request, $response)
		{
			return $response;
		});
	}

	/**
	 *
	 */
	public function testAllowWithInvalidDomain(): void
	{
		$middleware = new class extends AccessControlAllowOrigin
		{
			protected $allowedDomains =
			[
				'https://example.org',
			];
		};

		$request         = Mockery::mock(Request::class);
		$requestHeaders  = Mockery::mock(RequestHeaders::class);
		$response        = Mockery::mock(Response::class);
		$responseHeaders = Mockery::mock(ResponseHeaders::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($requestHeaders);

		$requestHeaders->shouldReceive('get')->once()->with('Origin')->andReturn('https://example.com');

		$middleware->execute($request, $response, function($request, $response)
		{
			return $response;
		});
	}

	/**
	 *
	 */
	public function testAllowWithNoOrigin(): void
	{
		$middleware = new class extends AccessControlAllowOrigin
		{
			protected $allowedDomains =
			[
				'https://example.org',
			];
		};

		$request         = Mockery::mock(Request::class);
		$requestHeaders  = Mockery::mock(RequestHeaders::class);
		$response        = Mockery::mock(Response::class);
		$responseHeaders = Mockery::mock(ResponseHeaders::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($requestHeaders);

		$requestHeaders->shouldReceive('get')->once()->with('Origin')->andReturn(null);

		$middleware->execute($request, $response, function($request, $response)
		{
			return $response;
		});
	}
}
