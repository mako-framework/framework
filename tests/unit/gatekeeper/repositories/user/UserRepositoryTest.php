<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\gatekeeper\repositories\user;

use Closure;
use InvalidArgumentException;
use mako\gatekeeper\entities\user\User;
use mako\gatekeeper\repositories\user\UserRepository;
use mako\tests\TestCase;
use Mockery;

/**
 * @group unit
 */
class UserRepositoryTest extends TestCase
{
	/**
	 *
	 */
	protected function getRepository(Closure $callback = null)
	{
		$repository = Mockery::mock(UserRepository::class . '[getModel]', ['mocked'])->makePartial();

		$repository->shouldAllowMockingProtectedMethods();

		$user = Mockery::mock(User::class)->shouldDeferMissing();

		if(!empty($callback))
		{
			$callback($user);
		}

		$repository->shouldReceive('getModel')->andReturn($user);

		return $repository;
	}

	/**
	 *
	 */
	public function testCreateUser(): void
	{
		$repository = $this->getRepository(function($user): void
		{
			$user->shouldReceive('save')->once();

			$user->shouldReceive('generateAccessToken')->once();

			$user->shouldReceive('generateActionToken')->once();
		});

		$user = $repository->createUser(['foo' => 'bar']);

		$this->assertSame('bar', $user->foo);
	}

	/**
	 *
	 */
	public function testGetByUsername(): void
	{
		$repository = $this->getRepository(function($user): void
		{
			$user->shouldReceive('where')->once()->with('username', '=', 'foobar')->andReturn($user);

			$user->shouldReceive('first')->once()->andReturn($user);
		});

		$this->assertInstanceOf(User::class, $repository->getByUsername('foobar'));
	}

	/**
	 *
	 */
	public function testGetById(): void
	{
		$repository = $this->getRepository(function($user): void
		{
			$user->shouldReceive('where')->once()->with('id', '=', 1)->andReturn($user);

			$user->shouldReceive('first')->once()->andReturn($user);
		});

		$this->assertInstanceOf(User::class, $repository->getById(1));
	}

	/**
	 *
	 */
	public function testGetByEmail(): void
	{
		$repository = $this->getRepository(function($user): void
		{
			$user->shouldReceive('where')->once()->with('email', '=', 'foobar')->andReturn($user);

			$user->shouldReceive('first')->once()->andReturn($user);
		});

		$this->assertInstanceOf(User::class, $repository->getByEmail('foobar'));
	}

	/**
	 *
	 */
	public function testGetByAccessToken(): void
	{
		$repository = $this->getRepository(function($user): void
		{
			$user->shouldReceive('where')->once()->with('access_token', '=', 'foobar')->andReturn($user);

			$user->shouldReceive('first')->once()->andReturn($user);
		});

		$this->assertInstanceOf(User::class, $repository->getByAccessToken('foobar'));
	}

	/**
	 *
	 */
	public function testGetByActionToken(): void
	{
		$repository = $this->getRepository(function($user): void
		{
			$user->shouldReceive('where')->once()->with('action_token', '=', 'foobar')->andReturn($user);

			$user->shouldReceive('first')->once()->andReturn($user);
		});

		$this->assertInstanceOf(User::class, $repository->getByActionToken('foobar'));
	}

	/**
	 *
	 */
	public function testGetByIdentifier(): void
	{
		$repository = $this->getRepository(function($user): void
		{
			$user->shouldReceive('where')->once()->with('email', '=', 'foobar')->andReturn($user);

			$user->shouldReceive('first')->once()->andReturn($user);

			$user->shouldReceive('where')->once()->with('username', '=', 'foobar')->andReturn($user);

			$user->shouldReceive('first')->once()->andReturn($user);

			$user->shouldReceive('where')->once()->with('id', '=', 1)->andReturn($user);

			$user->shouldReceive('first')->once()->andReturn($user);
		});

		$this->assertInstanceOf(User::class, $repository->getByIdentifier('foobar'));

		$repository->setIdentifier('username');

		$this->assertInstanceOf(User::class, $repository->getByIdentifier('foobar'));

		$repository->setIdentifier('id');

		$this->assertInstanceOf(User::class, $repository->getByIdentifier(1));
	}

	/**
	 *
	 */
	public function testSetInvalidIdentifier(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$this->expectExceptionMessage('Invalid identifier [ nope ].');

		$this->getRepository()->setIdentifier('nope');
	}
}
