<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\unit\auth\providers;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\auth\providers\GroupProvider;

/**
 * @group unit
 */
class GroupProviderTest extends PHPUnit_Framework_TestCase
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
	public function getGroup()
	{
		return Mockery::mock('overload:mako\auth\group\Group')->shouldDeferMissing();
	}

	/**
	 *
	 */
	public function testCreateGroup()
	{
		$group = $this->getGroup();

		$group->shouldReceive('setName')->once()->with('foobar');

		$group->shouldReceive('save')->once();

		$groupProvider = new GroupProvider($group);

		$this->assertInstanceOf('mako\auth\group\Group', $groupProvider->createGroup('foobar'));
	}

	/**
	 *
	 */
	public function testGetByName()
	{
		$group = $this->getGroup();

		$group->shouldReceive('where')->once()->with('name', '=', 'foobar')->andReturn($group);

		$group->shouldReceive('first')->once()->andReturn($group);

		$groupProvider = new GroupProvider($group);

		$this->assertInstanceOf('mako\auth\group\Group', $groupProvider->getByName('foobar'));
	}

	/**
	 *
	 */
	public function testGetById()
	{
		$group = $this->getGroup();

		$group->shouldReceive('where')->once()->with('id', '=', 1)->andReturn($group);

		$group->shouldReceive('first')->once()->andReturn($group);

		$groupProvider = new GroupProvider($group);

		$this->assertInstanceOf('mako\auth\group\Group', $groupProvider->getById(1));
	}
}