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

		$rateLimiter = Mockery::mock(RateLimiterInterface::class);

		$rateLimiter->shouldReceive('isLimitReached')->once()->with('/foo', 10)->andReturn(true);

		$rateLimiter->shouldReceive('getRetryAfter')->once()->andReturn(new DateTime);

		$route = Mockery::mock(Route::class);

		$route->shouldReceive('getRoute')->once()->andReturn('/foo');

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getRoute')->once()->andReturn($route);

		$response = Mockery::mock(Response::class);

		$middleware = new RateLimiter($rateLimiter, 10, '1 hour');

		$middleware->execute($request, $response, fn ($request, $response) => $response);
	}

	/**
	 *
	 */
	public function testLimitNotReached(): void
	{
		$retryAfter = Mockery::mock(DateTime::class);

		$retryAfter->shouldReceive('getTimestamp')->once()->andReturn(1234567890);

		$rateLimiter = Mockery::mock(RateLimiterInterface::class);

		$rateLimiter->shouldReceive('isLimitReached')->once()->with('/foo', 10)->andReturn(false);

		$rateLimiter->shouldReceive('increment')->once()->andReturn(1);

		$rateLimiter->shouldReceive('getRetryAfter')->once()->andReturn($retryAfter);

		$route = Mockery::mock(Route::class);

		$route->shouldReceive('getRoute')->once()->andReturn('/foo');

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getRoute')->once()->andReturn($route);

		$responseHeaders = Mockery::mock(ResponseHeaders::class);

		$responseHeaders->shouldReceive('add')->once()->with('X-RateLimit-Limit', 10);
		$responseHeaders->shouldReceive('add')->once()->with('X-RateLimit-Remaining', 9);
		$responseHeaders->shouldReceive('add')->once()->with('X-RateLimit-Reset', 1234567890);

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
		$rateLimiter = Mockery::mock(RateLimiterInterface::class);

		$rateLimiter->shouldReceive('isLimitReached')->once()->with('/foo', 10)->andReturn(false);

		$rateLimiter->shouldReceive('increment')->once();

		$route = Mockery::mock(Route::class);

		$route->shouldReceive('getRoute')->once()->andReturn('/foo');

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getRoute')->once()->andReturn($route);

		$response = Mockery::mock(Response::class);

		$middleware = new RateLimiter($rateLimiter, 10, '1 hour', false);

		$middleware->execute($request, $response, fn ($request, $response) => $response);
	}
}
