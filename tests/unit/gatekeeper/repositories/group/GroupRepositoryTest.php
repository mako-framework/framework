<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\gatekeeper\repositories\group;

use Closure;
use mako\gatekeeper\repositories\group\GroupRepository;
use mako\tests\TestCase;
use Mockery;

/**
 * @group unit
 */
class GroupRepositoryTest extends TestCase
{
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
	public function testCreateGroup(): void
	{
		$repository = $this->getRepository(function($group): void
		{
			$group->shouldReceive('save')->once();
		});

		$group = $repository->createGroup(['foo' => 'bar']);

		$this->assertSame('bar', $group->foo);
	}

	/**
	 *
	 */
	public function testGetByName(): void
	{
		$repository = $this->getRepository(function($group): void
		{
			$group->shouldReceive('where')->once()->with('name', '=', 'foobar')->andReturn($group);

			$group->shouldReceive('first')->once()->andReturn($group);
		});

		$this->assertInstanceOf(Group::class, $repository->getByName('foobar'));
	}

	/**
	 *
	 */
	public function testGetById(): void
	{
		$repository = $this->getRepository(function($group): void
		{
			$group->shouldReceive('where')->once()->with('id', '=', 1)->andReturn($group);

			$group->shouldReceive('first')->once()->andReturn($group);
		});

		$this->assertInstanceOf(Group::class, $repository->getById(1));
	}

	/**
	 *
	 */
	public function testGetByIdentifier(): void
	{
		$repository = $this->getRepository(function($group): void
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
	public function testSetInvalidIdentifier(): void
	{
		$this->getRepository()->setIdentifier('nope');
	}
}
