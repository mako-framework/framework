<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cache\stores\traits;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\cache\stores\traits\GetOrElseTrait;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class Store
{
	use GetOrElseTrait;

	public function has($key)
	{

	}

	public function get($key)
	{

	}

	public function put($key, $data, $ttl = 0)
	{

	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */
class GetOrElseTraitTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function testGetOrElse()
	{
		$closure = function(){ return 'from closure'; };

		//

		$store = Mockery::mock(Store::class . '[has,get]');

		$store->shouldReceive('has')->with('foo')->once()->andReturn(true);

		$store->shouldReceive('get')->with('foo')->once()->andReturn('from cache');

		$this->assertEquals('from cache', $store->getOrElse('foo', $closure));

		//

		$store = Mockery::mock(Store::class . '[has,get,put]');

		$store->shouldReceive('has')->with('foo')->once()->andReturn(false);

		$store->shouldReceive('get')->never();

		$store->shouldReceive('put')->with('foo', 'from closure', 0)->once();

		$this->assertEquals('from closure', $store->getOrElse('foo', $closure));

		//

		$store = Mockery::mock(Store::class . '[has,get,put]');

		$store->shouldReceive('has')->with('foo')->once()->andReturn(false);

		$store->shouldReceive('get')->never();

		$store->shouldReceive('put')->with('foo', 'from closure', 3600)->once();

		$this->assertEquals('from closure', $store->getOrElse('foo', $closure, 3600));
	}
}
