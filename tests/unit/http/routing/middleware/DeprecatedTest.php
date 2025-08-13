<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\routing\middleware;

use mako\http\exceptions\GoneException;
use mako\http\Request;
use mako\http\Response;
use mako\http\response\Headers;
use mako\http\routing\middleware\Deprecated;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class DeprecatedTest extends TestCase
{
	/**
	 *
	 */
	public function testConstructorWithoutArgs(): void
	{
		$this->expectExceptionMessage('You must specify either a deprecation date or a sunset date or both.');

		new Deprecated;
	}

	/**
	 *
	 */
	public function testConstructorWithSunsetBeforeDeprecation(): void
	{
		$this->expectExceptionMessage('The deprecation date must be earlier than the sunset date.');

		new Deprecated('2021-02-01', '2020-01-01');
	}

	/**
	 *
	 */
	public function testDeprecatedWithDeprecationDate(): void
	{
		$middleware = new Deprecated('2021-02-01');

		/** @var Mockery\MockInterface|Request $request */
		$request = Mockery::mock(Request::class);

		/** @var Mockery\MockInterface|Response $response */
		$response = Mockery::mock(Response::class);

		/** @var Headers|Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		(function () use ($headers): void {
			$this->headers = $headers;
		})->bindTo($response, Response::class)();

		$headers->shouldReceive('add')->once()->with('Deprecation', '@1612137600');

		$middleware->execute($request, $response, fn ($request, $response) => $response);

	}

	/**
	 *
	 */
	public function testDeprecatedWithSunsetDate(): void
	{
		$middleware = new Deprecated(null, '2021-02-01');

		/** @var Mockery\MockInterface|Request $request */
		$request = Mockery::mock(Request::class);

		/** @var Mockery\MockInterface|Response $response */
		$response = Mockery::mock(Response::class);

		/** @var Headers|Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		(function () use ($headers): void {
			$this->headers = $headers;
		})->bindTo($response, Response::class)();

		$headers->shouldReceive('add')->once()->with('Sunset', 'Mon, 01 Feb 2021 00:00:00 GMT');

		$middleware->execute($request, $response, fn ($request, $response) => $response);
	}

	/**
	 *
	 */
	public function testDeprecatedWithDeprecationAndSunsetDate(): void
	{
		$middleware = new Deprecated('2020-01-01', '2021-02-01');

		/** @var Mockery\MockInterface|Request $request */
		$request = Mockery::mock(Request::class);

		/** @var Mockery\MockInterface|Response $response */
		$response = Mockery::mock(Response::class);

		/** @var Headers|Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		(function () use ($headers): void {
			$this->headers = $headers;
		})->bindTo($response, Response::class)();

		$headers->shouldReceive('add')->once()->with('Deprecation', '@1577836800');

		$headers->shouldReceive('add')->once()->with('Sunset', 'Mon, 01 Feb 2021 00:00:00 GMT');

		$middleware->execute($request, $response, fn ($request, $response) => $response);
	}

	/**
	 *
	 */
	public function testDisableAfterSunset(): void
	{
		$this->expectException(GoneException::class);

		$middleware = new Deprecated(sunsetDate: '2021-02-01', disableAfterSunset: true);

		/** @var Mockery\MockInterface|Request $request */
		$request = Mockery::mock(Request::class);

		/** @var Mockery\MockInterface|Response $response */
		$response = Mockery::mock(Response::class);

		/** @var Headers|Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		(function () use ($headers): void {
			$this->headers = $headers;
		})->bindTo($response, Response::class)();

		$headers->shouldReceive('add')->once()->with('Sunset', 'Mon, 01 Feb 2021 00:00:00 GMT');

		$middleware->execute($request, $response, fn ($request, $response) => $response);
	}
}
