<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\throttle\http\routing\middleware;

use DateTime;
use mako\http\exceptions\TooManyRequestsException;
use mako\http\Request;
use mako\http\Response;
use mako\http\response\Headers as ResponseHeaders;
use mako\http\routing\Route;
use mako\tests\TestCase;
use mako\throttle\http\routing\middleware\RateLimiter;
use mako\throttle\RateLimiterInterface;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class RateLimiterTest extends TestCase
{
	/**
	 *
	 */
	public function testLimitReached(): void
	{
		$this->expectException(TooManyRequestsException::class);

		/** @var Mockery\MockInterface&RateLimiterInterface $rateLimiter */
		$rateLimiter = Mockery::mock(RateLimiterInterface::class);

		$rateLimiter->shouldReceive('isLimitReached')->once()->with('/foo', 10)->andReturn(true);

		$rateLimiter->shouldReceive('getRetryAfter')->once()->andReturn(new DateTime);

		/** @var Mockery\MockInterface&Route $route */
		$route = Mockery::mock(Route::class);

		$route->shouldReceive('getRoute')->once()->andReturn('/foo');

		/** @var Mockery\MockInterface&Request $request */
		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getRoute')->once()->andReturn($route);

		/** @var Mockery\MockInterface&Response $response */
		$response = Mockery::mock(Response::class);

		$middleware = new RateLimiter($rateLimiter, 10, '1 hour');

		$middleware->execute($request, $response, fn ($request, $response) => $response);
	}

	/**
	 *
	 */
	public function testLimitNotReached(): void
	{
		/** @var Mockery\MockInterface&RateLimiterInterface $retryAfter */
		$retryAfter = Mockery::mock(DateTime::class);

		$retryAfter->shouldReceive('getTimestamp')->once()->andReturn(1234567890);

		/** @var Mockery\MockInterface&RateLimiterInterface $rateLimiter */
		$rateLimiter = Mockery::mock(RateLimiterInterface::class);

		$rateLimiter->shouldReceive('isLimitReached')->once()->with('/foo', 10)->andReturn(false);

		$rateLimiter->shouldReceive('increment')->once()->andReturn(1);

		$rateLimiter->shouldReceive('getRetryAfter')->once()->andReturn($retryAfter);

		/** @var Mockery\MockInterface&Route $route */
		$route = Mockery::mock(Route::class);

		$route->shouldReceive('getRoute')->once()->andReturn('/foo');

		/** @var Mockery\MockInterface&Request $request */
		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getRoute')->once()->andReturn($route);

		/** @var Mockery\MockInterface|ResponseHeaders $responseHeaders */
		$responseHeaders = Mockery::mock(ResponseHeaders::class);

		$responseHeaders->shouldReceive('add')->once()->with('X-RateLimit-Limit', 10);
		$responseHeaders->shouldReceive('add')->once()->with('X-RateLimit-Remaining', 9);
		$responseHeaders->shouldReceive('add')->once()->with('X-RateLimit-Reset', 1234567890);

		/** @var Mockery\MockInterface&Response $response */
		$response = Mockery::mock(Response::class);

		(function () use ($responseHeaders): void {
			$this->headers = $responseHeaders;
		})->bindTo($response, Response::class)();

		$middleware = new RateLimiter($rateLimiter, 10, '1 hour');

		$middleware->execute($request, $response, fn ($request, $response) => $response);
	}

	/**
	 *
	 */
	public function testLimitNotReachedWithoutHeaders(): void
	{
		/** @var Mockery\MockInterface&RateLimiterInterface $rateLimiter */
		$rateLimiter = Mockery::mock(RateLimiterInterface::class);

		$rateLimiter->shouldReceive('isLimitReached')->once()->with('/foo', 10)->andReturn(false);

		$rateLimiter->shouldReceive('increment')->once();

		/** @var Mockery\MockInterface&Route $route */
		$route = Mockery::mock(Route::class);

		$route->shouldReceive('getRoute')->once()->andReturn('/foo');

		/** @var Mockery\MockInterface&Request $request */
		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getRoute')->once()->andReturn($route);

		/** @var Mockery\MockInterface&Response $response */
		$response = Mockery::mock(Response::class);

		$middleware = new RateLimiter($rateLimiter, 10, '1 hour', false);

		$middleware->execute($request, $response, fn ($request, $response) => $response);
	}
}
