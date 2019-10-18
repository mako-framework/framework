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

/**
 * @group unit
 */
class ContentNegotiationTraitTest extends TestCase
{
	/**
	 * @param  \mako\http\Request  $request  Request
	 * @param  \mako\http\Response $response Response
	 * @return object
	 */
	protected function getTestClass(Request $request, Response $response): object
	{
		return new class ($request, $response)
		{
			use ContentNegotiationTrait;

			protected $request;

			protected $response;

			public function __construct(Request $request, Response $response)
			{
				$this->request = $request;

				$this->response = $response;
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

		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['application/json']);

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($headers);

		$response = Mockery::mock(Response::class);

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testExpectsType(['application/json']));

		//

		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['foo/bar+json']);

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($headers);

		$response = Mockery::mock(Response::class);

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testExpectsType(['application/json'], '+json'));

		//

		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['foo/bar']);

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($headers);

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

		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['application/json']);

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($headers);

		$response = Mockery::mock(Response::class);

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testExpectsJson());

		//

		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['foo/bar+json']);

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($headers);

		$response = Mockery::mock(Response::class);

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testExpectsJson());

		//

		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['foo/bar']);

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($headers);

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

		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['application/xml']);

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($headers);

		$response = Mockery::mock(Response::class);

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testExpectsXml());

		//

		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['foo/bar+xml']);

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($headers);

		$response = Mockery::mock(Response::class);

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testExpectsXml());

		//

		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['foo/bar']);

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($headers);

		$response = Mockery::mock(Response::class);

		$test = $this->getTestClass($request, $response);

		$this->assertFalse($test->testExpectsXml());
	}

	/**
	 *
	 */
	public function testRespondWithType(): void
	{
		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('application/json');

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testRespondWithType(['application/json']));

		//

		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('foo/bar+json');

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testRespondWithType(['application/json'], '+json'));

		//

		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn([]);

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($headers);

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('foo/bar');

		$test = $this->getTestClass($request, $response);

		$this->assertFalse($test->testRespondWithType(['application/json'], '+json'));

		//

		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['application/json']);

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($headers);

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('text/html');

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testRespondWithType(['application/json']));

		//

		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['foo/bar+json']);

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($headers);

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('text/html');

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testRespondWithType(['application/json'], '+json'));

		//

		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['foo/bar']);

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($headers);

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
		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('application/json');

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testRespondWithJson());

		//

		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('text/json');

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testRespondWithJson());

		//

		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['application/json']);

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($headers);

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('text/html');

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testRespondWithJson());

		//

		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['text/json']);

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($headers);

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('text/html');

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testRespondWithJson());

		//

		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['foo/bar+json']);

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($headers);

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('text/html');

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testRespondWithJson());

		//

		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['foo/bar']);

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($headers);

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
		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('application/xml');

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testRespondWithXml());

		//

		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('text/xml');

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testRespondWithXml());

		//

		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['application/xml']);

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($headers);

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('text/html');

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testRespondWithXml());

		//

		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['text/xml']);

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($headers);

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('text/html');

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testRespondWithXml());

		//

		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['foo/bar+xml']);

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($headers);

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('text/html');

		$test = $this->getTestClass($request, $response);

		$this->assertTrue($test->testRespondWithXml());

		//

		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['foo/bar']);

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($headers);

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('text/html');

		$test = $this->getTestClass($request, $response);

		$this->assertFalse($test->testRespondWithXml());
	}
}
