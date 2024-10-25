<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\traits;

use mako\http\Request;
use mako\http\request\Headers;
use mako\http\Response;
use mako\http\traits\ContentNegotiationTrait;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class ContentNegotiationTraitTest extends TestCase
{
	/**
	 *
	 */
	protected function getTestClass(Request $request, Response $response): object
	{
		return new class ($request, $response) {
			use ContentNegotiationTrait;

			public function __construct(
				protected Request $request,
				protected Response $response
			) {
			}

			public function testExpectsType(array $mimeTypes, ?string $suffix = null): bool
			{
				return $this->expectsType($mimeTypes, $suffix);
			}

			public function testExpectsJson(): bool
			{
				return $this->expectsJson();
			}

			public function testExpectsXml(): bool
			{
				return $this->expectsXml();
			}

			public function testRespondWithType(array $mimeTypes, ?string $suffix = null): bool
			{
				return $this->respondWithType($mimeTypes, $suffix);
			}

			public function testRespondWithJson(): bool
			{
				return $this->respondWithJson();
			}

			public function testRespondWithXml(): bool
			{
				return $this->respondWithXml();
			}
		};
	}

	/**
	 *
	 */
	public function testExpectsType(): void
	{
		//

		/** @var \mako\http\request\Headers|\Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['application/json']);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		(function () use ($headers): void {
			$this->headers = $headers;
		})->bindTo($request, Request::class)();

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testExpectsType(['application/json']));

		//

		/** @var \mako\http\request\Headers|\Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['foo/bar+json']);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		(function () use ($headers): void {
			$this->headers = $headers;
		})->bindTo($request, Request::class)();

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testExpectsType(['application/json'], '+json'));

		//

		/** @var \mako\http\request\Headers|\Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['foo/bar']);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		(function () use ($headers): void {
			$this->headers = $headers;
		})->bindTo($request, Request::class)();

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$test = $this->getTestClass($request, $response);

		$this->assertFalse($test->testExpectsType(['application/json'], '+json'));
	}

	/**
	 *
	 */
	public function testExpectsJson(): void
	{
		//

		/** @var \mako\http\request\Headers|\Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['application/json']);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		(function () use ($headers): void {
			$this->headers = $headers;
		})->bindTo($request, Request::class)();

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testExpectsJson());

		//

		/** @var \mako\http\request\Headers|\Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['foo/bar+json']);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		(function () use ($headers): void {
			$this->headers = $headers;
		})->bindTo($request, Request::class)();

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testExpectsJson());

		//

		/** @var \mako\http\request\Headers|\Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['foo/bar']);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		(function () use ($headers): void {
			$this->headers = $headers;
		})->bindTo($request, Request::class)();

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$test = $this->getTestClass($request, $response);

		$this->assertFalse($test->testExpectsJson());
	}

	/**
	 *
	 */
	public function testExpectsXml(): void
	{
		//

		/** @var \mako\http\request\Headers|\Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['application/xml']);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		(function () use ($headers): void {
			$this->headers = $headers;
		})->bindTo($request, Request::class)();

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testExpectsXml());

		//

		/** @var \mako\http\request\Headers|\Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['foo/bar+xml']);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		(function () use ($headers): void {
			$this->headers = $headers;
		})->bindTo($request, Request::class)();

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testExpectsXml());

		//

		/** @var \mako\http\request\Headers|\Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['foo/bar']);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		(function () use ($headers): void {
			$this->headers = $headers;
		})->bindTo($request, Request::class)();

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$test = $this->getTestClass($request, $response);

		$this->assertFalse($test->testExpectsXml());
	}

	/**
	 *
	 */
	public function testRespondWithType(): void
	{
		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('application/json');

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testRespondWithType(['application/json']));

		//

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('foo/bar+json');

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testRespondWithType(['application/json'], '+json'));

		//

		/** @var \mako\http\request\Headers|\Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn([]);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		(function () use ($headers): void {
			$this->headers = $headers;
		})->bindTo($request, Request::class)();

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('foo/bar');

		$test = $this->getTestClass($request, $response);

		$this->assertFalse($test->testRespondWithType(['application/json'], '+json'));

		//

		/** @var \mako\http\request\Headers|\Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['application/json']);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		(function () use ($headers): void {
			$this->headers = $headers;
		})->bindTo($request, Request::class)();

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('text/html');

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testRespondWithType(['application/json']));

		//

		/** @var \mako\http\request\Headers|\Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['foo/bar+json']);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		(function () use ($headers): void {
			$this->headers = $headers;
		})->bindTo($request, Request::class)();

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('text/html');

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testRespondWithType(['application/json'], '+json'));

		//

		/** @var \mako\http\request\Headers|\Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['foo/bar']);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		(function () use ($headers): void {
			$this->headers = $headers;
		})->bindTo($request, Request::class)();

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('text/html');

		$test = $this->getTestClass($request, $response);

		$this->assertFalse($test->testRespondWithType(['application/json'], '+json'));
	}

	/**
	 *
	 */
	public function testRespondWithJson(): void
	{
		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('application/json');

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testRespondWithJson());

		//

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('text/json');

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testRespondWithJson());

		//

		/** @var \mako\http\request\Headers|\Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['application/json']);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		(function () use ($headers): void {
			$this->headers = $headers;
		})->bindTo($request, Request::class)();

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('text/html');

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testRespondWithJson());

		//

		/** @var \mako\http\request\Headers|\Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['text/json']);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		(function () use ($headers): void {
			$this->headers = $headers;
		})->bindTo($request, Request::class)();

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('text/html');

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testRespondWithJson());

		//

		/** @var \mako\http\request\Headers|\Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['foo/bar+json']);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		(function () use ($headers): void {
			$this->headers = $headers;
		})->bindTo($request, Request::class)();

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('text/html');

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testRespondWithJson());

		//

		/** @var \mako\http\request\Headers|\Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['foo/bar']);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		(function () use ($headers): void {
			$this->headers = $headers;
		})->bindTo($request, Request::class)();

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('text/html');

		$test = $this->getTestClass($request, $response);

		$this->assertFalse($test->testRespondWithJson());
	}

	/**
	 *
	 */
	public function testRespondWithXml(): void
	{
		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('application/xml');

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testRespondWithXml());

		//

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('text/xml');

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testRespondWithXml());

		//

		/** @var \mako\http\request\Headers|\Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['application/xml']);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		(function () use ($headers): void {
			$this->headers = $headers;
		})->bindTo($request, Request::class)();

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('text/html');

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testRespondWithXml());

		//

		/** @var \mako\http\request\Headers|\Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['text/xml']);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		(function () use ($headers): void {
			$this->headers = $headers;
		})->bindTo($request, Request::class)();

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('text/html');

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testRespondWithXml());

		//

		/** @var \mako\http\request\Headers|\Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['foo/bar+xml']);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		(function () use ($headers): void {
			$this->headers = $headers;
		})->bindTo($request, Request::class)();

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('text/html');

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testRespondWithXml());

		//

		/** @var \mako\http\request\Headers|\Mockery\MockInterface $headers */
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['foo/bar']);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		(function () use ($headers): void {
			$this->headers = $headers;
		})->bindTo($request, Request::class)();

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('text/html');

		$test = $this->getTestClass($request, $response);

		$this->assertFalse($test->testRespondWithXml());
	}
}
