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

		$response->shouldReceive('type')->once()->with('application/json');

		$response->shouldReceive('body')->once()->with('[1,2,3]');

		//

		$json = new JSON([1, 2, 3]);

		$json->build($request, $response);
	}

	/**
	 *
	 */
	public function testBuildWithStatus(): void
	{
		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('type')->once()->with('application/json');

		$response->shouldReceive('status')->once()->with(400);

		$response->shouldReceive('body')->once()->with('[1,2,3]');

		//

		$json = new JSON([1, 2, 3]);

		$json->status(400);

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

		$response->shouldReceive('type')->once()->with('text/javascript');

		$response->shouldReceive('body')->once()->with('/**/jsonp([1,2,3]);');

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

		$response->shouldReceive('type')->once()->with('text/javascript');

		$response->shouldReceive('body')->once()->with('/**/callback([1,2,3]);');

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

		$response->shouldReceive('type')->once()->with('application/json');

		$response->shouldReceive('body')->once()->with('[1,2,3]');

		//

		$json = new JSON([1, 2, 3]);

		$json->asJsonpWith('callback');

		$json->build($request, $response);
	}
}
