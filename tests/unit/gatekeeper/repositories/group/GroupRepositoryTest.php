<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\gatekeeper\repositories\group;

use Closure;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\gatekeeper\entities\group\Grup;
use mako\gatekeeper\repositories\group\GroupRepository;

/**
 * @group unit
 */
class GroupRepositoryTest extends PHPUnit_Framework_TestCase
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
	protected function getRepository(Closure $callback = null)
	{
		$repository = Mockery::mock(GroupRepository::class . '[getModel]', ['mocked'])->makePartial();

		$repository->shouldAllowMockingProtectedMethods();

		$group = Mockery::mock(Group::class)->shouldDeferMissing();

		if(!empty($callback))
		{
			$callback($group);
		}

		$repository->shouldReceive('getModel')->andReturn($group);

		return $repository;
	}

	/**
	 *
	 */
	public function testCreateGroup()
	{
		$repository = $this->getRepository(function($group)
		{
			$group->shouldReceive('save')->once();
		});

		$group = $repository->createGroup(['foo' => 'bar']);

		$this->assertSame('bar', $group->foo);
	}

	/**
	 *
	 */
	public function testGetByName()
	{
		$repository = $this->getRepository(function($group)
		{
			$group->shouldReceive('where')->once()->with('name', '=', 'foobar')->andReturn($group);

			$group->shouldReceive('first')->once()->andReturn($group);
		});

		$this->assertInstanceOf(Group::class, $repository->getByName('foobar'));
	}

	/**
	 *
	 */
	public function testGetById()
	{
		$repository = $this->getRepository(function($group)
		{
			$group->shouldReceive('where')->once()->with('id', '=', 1)->andReturn($group);

			$group->shouldReceive('first')->once()->andReturn($group);
		});

		$this->assertInstanceOf(Group::class, $repository->getById(1));
	}

	/**
	 *
	 */
	public function testGetByIdentifier()
	{
		$repository = $this->getRepository(function($group)
		{
			$group->shouldReceive('where')->once()->with('name', '=', 'foobar')->andReturn($group);

			$group->shouldReceive('first')->once()->andReturn($group);

			$group->shouldReceive('where')->once()->with('id', '=', 1)->andReturn($group);

			$group->shouldReceive('first')->once()->andReturn($group);
		});

		$this->assertInstanceOf(Group::class, $repository->getByIdentifier('foobar'));

		$repository->setIdentifier('id');

		$this->assertInstanceOf(Group::class, $repository->getByIdentifier(1));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage Invalid identifier [ nope ].
	 */
	public function testSetInvalidIdentifier()
	{
		$this->getRepository()->setIdentifier('nope');
	}
}
