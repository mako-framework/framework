<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\response\builders;

use Mockery;
use PHPUnit_Framework_TestCase;

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
		$request = Mockery::mock('mako\http\Request');

		$response = Mockery::mock('mako\http\Response');

		$response->shouldReceive('type')->once()->with('application/json');

		$response->shouldReceive('body')->once()->with('[1,2,3]');

		//

		$jsonp = new JSON([1,2,3]);

		$jsonp->build($request, $response);
	}
}
