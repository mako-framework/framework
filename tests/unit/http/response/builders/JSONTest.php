<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\response\builders;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\http\Request;
use mako\http\Response;
use mako\http\request\Parameters;
use mako\http\response\builders\JSON;

/**
 * @group unit
 */
class JSONTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function tearDown()
	{
		Mockery::close();
	}

	/**
	 *
	 */
	public function testBuild()
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
	public function testBuildWithJsonpWithCallback()
	{
		$query = Mockery::mock(Parameters::class);

		$query->shouldReceive('get')->once()->with('callback')->andReturn('jsonp');

		$request = Mockery::mock(Request::class);

		$request->query = $query;

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
	public function testBuildWithJsonpWithInvalidCallback()
	{
		$query = Mockery::mock(Parameters::class);

		$query->shouldReceive('get')->once()->with('callback')->andReturn('foo bar');

		$request = Mockery::mock(Request::class);

		$request->query = $query;

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
	public function testBuildWithJsonpWithoutCallback()
	{
		$query = Mockery::mock(Parameters::class);

		$query->shouldReceive('get')->once()->with('callback')->andReturn(null);

		$request = Mockery::mock(Request::class);

		$request->query = $query;

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('type')->once()->with('application/json');

		$response->shouldReceive('body')->once()->with('[1,2,3]');

		//

		$json = new JSON([1, 2, 3]);

		$json->asJsonpWith('callback');

		$json->build($request, $response);
	}
}
