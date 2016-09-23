<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\response\builders;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\http\response\builders\JSONP;

/**
 * @group unit
 */
class JSONPTest extends PHPUnit_Framework_TestCase
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
		$request = Mockery::mock('mako\http\Request');

		$request->shouldReceive('get')->once()->with('callback', 'callback')->andReturn('callback');

		$response = Mockery::mock('mako\http\Response');

		$response->shouldReceive('type')->once()->with('text/javascript');

		$response->shouldReceive('body')->once()->with('callback([1,2,3]);');

		//

		$jsonp = new JSONP([1,2,3]);

		$jsonp->build($request, $response);
	}

	/**
	 *
	 */
	public function testBuildCustomDefaultCallback()
	{
		$request = Mockery::mock('mako\http\Request');

		$request->shouldReceive('get')->once()->with('callback', 'foobar')->andReturn('foobar');

		$response = Mockery::mock('mako\http\Response');

		$response->shouldReceive('type')->once()->with('text/javascript');

		$response->shouldReceive('body')->once()->with('foobar([1,2,3]);');

		//

		$jsonp = new JSONP([1,2,3]);

		$jsonp->callback('foobar');

		$jsonp->build($request, $response);
	}

	/**
	 *
	 */
	public function testBuildWithCustomCallback()
	{
		$request = Mockery::mock('mako\http\Request');

		$request->shouldReceive('get')->once()->with('callback', 'callback')->andReturn('foobar');

		$response = Mockery::mock('mako\http\Response');

		$response->shouldReceive('type')->once()->with('text/javascript');

		$response->shouldReceive('body')->once()->with('foobar([1,2,3]);');

		//

		$jsonp = new JSONP([1,2,3]);

		$jsonp->build($request, $response);
	}

	/**
	 *
	 */
	public function testBuildWithInvalidCustomCallback()
	{
		$request = Mockery::mock('mako\http\Request');

		$request->shouldReceive('get')->once()->with('callback', 'callback')->andReturn('foo-bar');

		$response = Mockery::mock('mako\http\Response');

		$response->shouldReceive('type')->once()->with('text/javascript');

		$response->shouldReceive('body')->once()->with('callback([1,2,3]);');

		//

		$jsonp = new JSONP([1,2,3]);

		$jsonp->build($request, $response);
	}

	/**
	 *
	 */
	public function testBuildWithCustomKey()
	{
		$request = Mockery::mock('mako\http\Request');

		$request->shouldReceive('get')->once()->with('function', 'callback')->andReturn('callback');

		$response = Mockery::mock('mako\http\Response');

		$response->shouldReceive('type')->once()->with('text/javascript');

		$response->shouldReceive('body')->once()->with('callback([1,2,3]);');

		//

		$jsonp = new JSONP([1,2,3]);

		$jsonp->key('function');

		$jsonp->build($request, $response);
	}
}