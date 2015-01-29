<?php

namespace mako\tests\unit\auth\providers;

use mako\auth\providers\GroupProvider;

use \Mockery as m;

/**
 * @group unit
 */

class GroupProviderTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function tearDown()
	{
		m::close();
	}

	/**
	 *
	 */

	public function getGroup()
	{
		return m::mock('overload:mako\auth\group\Group')->shouldDeferMissing();
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