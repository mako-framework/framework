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
use mako\tests\TestCase;
use Mockery;

/**
 * @group unit
 */
class JSONTest extends TestCase
{
	/**
	 *
	 */
	public function testBuild(): void
	{
		$request = Mockery::mock(Request::class);

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
		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setType')->once()->with('application/json');

		$response->shouldReceive('setStatus')->once()->with(400);

		$response->shouldReceive('setBody')->once()->with('[1,2,3]');

		//

		$json = new JSON([1, 2, 3]);

		$json->setStatus(400);

		$this->assertSame(400, $json->getStatus());

		$json->build($request, $response);
	}

	/**
	 *
	 */
	public function testBuildWithCharset(): void
	{
		$request = Mockery::mock(Request::class);

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
		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setType')->once()->with('application/json');

		$response->shouldReceive('setStatus')->once()->with(200);

		$response->shouldReceive('setCharset')->once()->with('UTF-8');

		$response->shouldReceive('setBody')->once()->with('[1,2,3]');

		//

		$json = new JSON([1, 2, 3], 0, 200, 'UTF-8');

		$this->assertSame(200, $json->getStatus());

		$this->assertSame('UTF-8', $json->getCharset());

		$json->build($request, $response);
	}

	/**
	 *
	 */
	public function testBuildWithJsonpWithCallback(): void
	{
		$query = Mockery::mock(Parameters::class);

		$query->shouldReceive('get')->once()->with('callback')->andReturn('jsonp');

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getQuery')->once()->andReturn($query);

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
		$query = Mockery::mock(Parameters::class);

		$query->shouldReceive('get')->once()->with('callback')->andReturn('foo bar');

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getQuery')->once()->andReturn($query);

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
		$query = Mockery::mock(Parameters::class);

		$query->shouldReceive('get')->once()->with('callback')->andReturn(null);

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getQuery')->once()->andReturn($query);

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setType')->once()->with('application/json');

		$response->shouldReceive('setBody')->once()->with('[1,2,3]');

		//

		$json = new JSON([1, 2, 3]);

		$json->asJsonpWith('callback');

		$json->build($request, $response);
	}
}
