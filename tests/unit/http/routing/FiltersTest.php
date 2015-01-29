<?php

namespace mako\tests\unit\http\routing;

use mako\http\routing\Filters;

use \Mockery as m;

/**
 * @group unit
 */

class FiltersTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function testRegister()
	{
		$filters = new Filters;

		$filters->register('foo', function(){});

		$filters->register('bar', 'MyFilter');
	}

	/**
	 *
	 */

	public function testGet()
	{
		$filters = new Filters;

		$filters->register('foo', function(){});

		$filters->register('bar', 'MyFilter');

		$this->assertInstanceOf('Closure', $filters->get('foo'));

		$this->assertSame('MyFilter', $filters->get('bar'));
	}

	/**
	 * @expectedException \RuntimeException
	 */

	public function testGetNonExisting()
	{
		$filters = new Filters;

		$filters->get('foo');
	}
}