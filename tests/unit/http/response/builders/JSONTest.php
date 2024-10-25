<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\response\builders;

use mako\http\Request;
use mako\http\request\Parameters;
use mako\http\Response;
use mako\http\response\builders\JSON;
use mako\http\response\Status;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class JSONTest extends TestCase
{
	/**
	 *
	 */
	public function testBuild(): void
	{
		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setType')->once()->with('application/json');

		$response->shouldReceive('setBody')->once()->with('[1,2,3]');

		//

		$json = new JSON([1, 2, 3]);

		$this->assertNull($json->getCharset());

		$this->assertNull($json->getStatus());

		$json->build($request, $response);
	}

	/**
	 *
	 */
	public function testBuildWithStatus(): void
	{
		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setType')->once()->with('application/json');

		$response->shouldReceive('setStatus')->once()->with(Status::BAD_REQUEST);

		$response->shouldReceive('setBody')->once()->with('[1,2,3]');

		//

		$json = new JSON([1, 2, 3]);

		$json->setStatus(400);

		$this->assertSame(Status::BAD_REQUEST, $json->getStatus());

		$json->build($request, $response);
	}

	/**
	 *
	 */
	public function testBuildWithCharset(): void
	{
		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setType')->once()->with('application/json');

		$response->shouldReceive('setCharset')->once()->with('UTF-8');

		$response->shouldReceive('setBody')->once()->with('[1,2,3]');

		//

		$json = new JSON([1, 2, 3]);

		$json->setCharset('UTF-8');

		$this->assertSame('UTF-8', $json->getCharset());

		$json->build($request, $response);
	}

	/**
	 *
	 */
	public function testBuildWithStatusAndCharsetFromConstructor(): void
	{
		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setType')->once()->with('application/json');

		$response->shouldReceive('setStatus')->once()->with(Status::OK);

		$response->shouldReceive('setCharset')->once()->with('UTF-8');

		$response->shouldReceive('setBody')->once()->with('[1,2,3]');

		//

		$json = new JSON([1, 2, 3], 0, 200, 'UTF-8');

		$this->assertSame(Status::OK, $json->getStatus());

		$this->assertSame('UTF-8', $json->getCharset());

		$json->build($request, $response);
	}

	/**
	 *
	 */
	public function testBuildWithJsonpWithCallback(): void
	{
		/** @var \mako\http\request\Parameters|\Mockery\MockInterface $query */
		$query = Mockery::mock(Parameters::class);

		$query->shouldReceive('get')->once()->with('callback')->andReturn('jsonp');

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		(function () use ($query): void {
			$this->query = $query;
		})->bindTo($request, Request::class)();

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setType')->once()->with('text/javascript');

		$response->shouldReceive('setBody')->once()->with('/**/jsonp([1,2,3]);');

		//

		$json = new JSON([1, 2, 3]);

		$json->asJsonpWith('callback');

		$json->build($request, $response);
	}

	/**
	 *
	 */
	public function testBuildWithJsonpWithInvalidCallback(): void
	{
		/** @var \mako\http\request\Parameters|\Mockery\MockInterface $query */
		$query = Mockery::mock(Parameters::class);

		$query->shouldReceive('get')->once()->with('callback')->andReturn('foo bar');

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		(function () use ($query): void {
			$this->query = $query;
		})->bindTo($request, Request::class)();

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setType')->once()->with('text/javascript');

		$response->shouldReceive('setBody')->once()->with('/**/callback([1,2,3]);');

		//

		$json = new JSON([1, 2, 3]);

		$json->asJsonpWith('callback');

		$json->build($request, $response);
	}

	/**
	 *
	 */
	public function testBuildWithJsonpWithoutCallback(): void
	{
		/** @var \mako\http\request\Parameters|\Mockery\MockInterface $query */
		$query = Mockery::mock(Parameters::class);

		$query->shouldReceive('get')->once()->with('callback')->andReturn(null);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		(function () use ($query): void {
			$this->query = $query;
		})->bindTo($request, Request::class)();

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setType')->once()->with('application/json');

		$response->shouldReceive('setBody')->once()->with('[1,2,3]');

		//

		$json = new JSON([1, 2, 3]);

		$json->asJsonpWith('callback');

		$json->build($request, $response);
	}
}
