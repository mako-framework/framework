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
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class SecurityHeadersTest extends TestCase
{
	/**
	 *
	 */
	public function testWithDefaultConfig(): void
	{
		/** @var Container|Mockery\MockInterface $container */
		$container = Mockery::mock(Container::class);

		/** @var Mockery\MockInterface|Request $request */
		$request = Mockery::mock(Request::class);

		/** @var Mockery\MockInterface|Response $response */
		$response = Mockery::mock(Response::class);

		/** @var Headers|Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('add')->once()->with('X-Content-Type-Options', 'nosniff');

		$headers->shouldReceive('add')->once()->with('X-Frame-Options', 'sameorigin');

		$headers->shouldReceive('add')->once()->with('X-XSS-Protection', '1; mode=block');

		(function () use ($headers): void {
			$this->headers = $headers;
		})->bindTo($response, Response::class)();

		$next = function ($request, $response) {
			$this->assertInstanceOf(Request::class, $request);

			$this->assertInstanceOf(Response::class, $response);

			return $response;
		};

		$securityHeaders = new SecurityHeaders($container);

		$securityHeaders->execute($request, $response, $next);
	}
}
