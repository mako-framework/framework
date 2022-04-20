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

/**
 * @group unit
 */
class AccessControlTest extends TestCase
{
	/**
	 *
	 */
	public function testAllowAll(): void
	{
		$middleware = new class extends AccessControl
		{
			protected $allowAllDomains = true;
		};

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request         = Mockery::mock(Request::class);
		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response        = Mockery::mock(Response::class);
		/** @var \mako\http\response\Headers|\Mockery\MockInterface $responseHeaders */
		$responseHeaders = Mockery::mock(ResponseHeaders::class);

		$response->shouldReceive('getHeaders')->once()->andReturn($responseHeaders);

		$responseHeaders->shouldReceive('add')->with('Access-Control-Allow-Origin', '*')->once();

		$middleware->execute($request, $response, function ($request, $response)
		{
			return $response;
		});
	}

	/**
	 *
	 */
	public function testAllowWithValidDomain(): void
	{
		$middleware = new class extends AccessControl
		{
			protected $allowedDomains =
			[
				'https://example.org',
			];
		};

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request         = Mockery::mock(Request::class);
		/** @var \mako\http\request\Headers|\Mockery\MockInterface $requestHeaders */
		$requestHeaders  = Mockery::mock(RequestHeaders::class);
		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response        = Mockery::mock(Response::class);
		/** @var \mako\http\response\Headers|\Mockery\MockInterface $responseHeaders */
		$responseHeaders = Mockery::mock(ResponseHeaders::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($requestHeaders);

		$requestHeaders->shouldReceive('get')->once()->with('Origin')->andReturn('https://example.org');

		$response->shouldReceive('getHeaders')->times(2)->andReturn($responseHeaders);

		$responseHeaders->shouldReceive('add')->with('Access-Control-Allow-Origin', 'https://example.org')->once();

		$responseHeaders->shouldReceive('add')->with('Vary', 'Origin', false)->once();

		$middleware->execute($request, $response, function ($request, $response)
		{
			return $response;
		});
	}

	/**
	 *
	 */
	public function testAllowWithInvalidDomain(): void
	{
		$middleware = new class extends AccessControl
		{
			protected $allowedDomains =
			[
				'https://example.org',
			];
		};

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request         = Mockery::mock(Request::class);
		/** @var \mako\http\request\Headers|\Mockery\MockInterface $requestHeaders */
		$requestHeaders  = Mockery::mock(RequestHeaders::class);
		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response        = Mockery::mock(Response::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($requestHeaders);

		$requestHeaders->shouldReceive('get')->once()->with('Origin')->andReturn('https://example.com');

		$middleware->execute($request, $response, function ($request, $response)
		{
			return $response;
		});
	}

	/**
	 *
	 */
	public function testAllowWithNoOrigin(): void
	{
		$middleware = new class extends AccessControl
		{
			protected $allowedDomains =
			[
				'https://example.org',
			];
		};

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request         = Mockery::mock(Request::class);
		/** @var \mako\http\request\Headers|\Mockery\MockInterface $requestHeaders */
		$requestHeaders  = Mockery::mock(RequestHeaders::class);
		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response        = Mockery::mock(Response::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($requestHeaders);

		$requestHeaders->shouldReceive('get')->once()->with('Origin')->andReturn(null);

		$middleware->execute($request, $response, function ($request, $response)
		{
			return $response;
		});
	}

	/**
	 *
	 */
	public function testAllowsCredentials(): void
	{
		$middleware = new class extends AccessControl
		{
			protected $allowAllDomains = true;
			protected $allowCredentials = true;
		};

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request         = Mockery::mock(Request::class);
		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response        = Mockery::mock(Response::class);
		/** @var \mako\http\response\Headers|\Mockery\MockInterface $responseHeaders */
		$responseHeaders = Mockery::mock(ResponseHeaders::class);

		$response->shouldReceive('getHeaders')->times(2)->andReturn($responseHeaders);

		$responseHeaders->shouldReceive('add')->with('Access-Control-Allow-Origin', '*')->once();

		$responseHeaders->shouldReceive('add')->with('Access-Control-Allow-Credentials', 'true')->once();

		$middleware->execute($request, $response, function ($request, $response)
		{
			return $response;
		});
	}

	/**
	 *
	 */
	public function testAllowHeaders(): void
	{
		$middleware = new class extends AccessControl
		{
			protected $allowAllDomains = true;
			protected $allowedHeaders = ['X-Custom-Header1', 'X-Custom-Header2'];
		};

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request         = Mockery::mock(Request::class);
		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response        = Mockery::mock(Response::class);
		/** @var \mako\http\response\Headers|\Mockery\MockInterface $responseHeaders */
		$responseHeaders = Mockery::mock(ResponseHeaders::class);

		$response->shouldReceive('getHeaders')->times(2)->andReturn($responseHeaders);

		$responseHeaders->shouldReceive('add')->with('Access-Control-Allow-Origin', '*')->once();

		$responseHeaders->shouldReceive('add')->with('Access-Control-Allow-Headers', 'X-Custom-Header1, X-Custom-Header2')->once();

		$middleware->execute($request, $response, function ($request, $response)
		{
			return $response;
		});
	}

	/**
	 *
	 */
	public function testAllowMethods(): void
	{
		$middleware = new class extends AccessControl
		{
			protected $allowAllDomains = true;
			protected $allowedMethods = ['GET', 'POST'];
		};

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request         = Mockery::mock(Request::class);
		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response        = Mockery::mock(Response::class);
		/** @var \mako\http\response\Headers|\Mockery\MockInterface $responseHeaders */
		$responseHeaders = Mockery::mock(ResponseHeaders::class);

		$response->shouldReceive('getHeaders')->times(2)->andReturn($responseHeaders);

		$responseHeaders->shouldReceive('add')->with('Access-Control-Allow-Origin', '*')->once();

		$responseHeaders->shouldReceive('add')->with('Access-Control-Allow-Methods', 'GET, POST')->once();

		$middleware->execute($request, $response, function ($request, $response)
		{
			return $response;
		});
	}
}
