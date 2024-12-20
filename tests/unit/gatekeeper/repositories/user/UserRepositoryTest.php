<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\gatekeeper\repositories\user;

use Closure;
use mako\gatekeeper\authorization\AuthorizerInterface;
use mako\gatekeeper\entities\user\User;
use mako\gatekeeper\exceptions\GatekeeperException;
use mako\gatekeeper\repositories\user\UserRepository;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class UserRepositoryTest extends TestCase
{
	/**
	 * @return Mockery\MockInterface|UserRepository
	 */
	protected function getRepository(?Closure $callback = null, ?AuthorizerInterface $authorizer = null)
	{
		/** @var \mako\gatekeeper\repositories\user\UserRepositoryInterface|Mockery\MockInterface $repository */
		$repository = Mockery::mock(UserRepository::class, ['mocked', $authorizer]);

		$repository = $repository->makePartial();

		$repository->shouldAllowMockingProtectedMethods();

		/** @var \mako\gatekeeper\entities\user\UserEntityInterface|Mockery\MockInterface $user */
		$user = Mockery::mock(User::class);

		$user = $user->makePartial();

		if (!empty($callback)) {
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
		$repository = $this->getRepository(function ($user): void {
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
		$repository = $this->getRepository(function ($user): void {
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
		$repository = $this->getRepository(function ($user): void {
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
		$repository = $this->getRepository(function ($user): void {
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
		$repository = $this->getRepository(function ($user): void {
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
		$repository = $this->getRepository(function ($user): void {
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
		$repository = $this->getRepository(function ($user): void {
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
		$this->expectException(GatekeeperException::class);

		$this->expectExceptionMessage('Invalid identifier [ nope ].');

		$this->getRepository()->setIdentifier('nope');
	}

	/**
	 *
	 */
	public function testCreateUserWithAuthorizer(): void
	{
		/** @var AuthorizerInterface|Mockery\MockInterface $authorizer */
		$authorizer = Mockery::mock(AuthorizerInterface::class);

		$repository = $this->getRepository(function ($user) use ($authorizer): void {
			$user->shouldReceive('save')->once();

			$user->shouldReceive('generateAccessToken')->once();

			$user->shouldReceive('generateActionToken')->once();

			$user->shouldReceive('setAuthorizer')->once()->with($authorizer);
		}, $authorizer);

		$user = $repository->createUser(['foo' => 'bar']);

		$this->assertSame('bar', $user->foo);
	}

	/**
	 *
	 */
	public function testGetByUsernameWithAuthorizer(): void
	{
		/** @var AuthorizerInterface|Mockery\MockInterface $authorizer */
		$authorizer = Mockery::mock(AuthorizerInterface::class);

		$repository = $this->getRepository(function ($user) use ($authorizer): void {
			$user->shouldReceive('where')->once()->with('username', '=', 'foobar')->andReturn($user);

			$user->shouldReceive('first')->once()->andReturn($user);

			$user->shouldReceive('setAuthorizer')->once()->with($authorizer);
		}, $authorizer);

		$this->assertInstanceOf(User::class, $repository->getByUsername('foobar'));
	}

	/**
	 *
	 */
	public function testGetByIdWithAuthorizer(): void
	{
		/** @var AuthorizerInterface|Mockery\MockInterface $authorizer */
		$authorizer = Mockery::mock(AuthorizerInterface::class);

		$repository = $this->getRepository(function ($user) use ($authorizer): void {
			$user->shouldReceive('where')->once()->with('id', '=', 1)->andReturn($user);

			$user->shouldReceive('first')->once()->andReturn($user);

			$user->shouldReceive('setAuthorizer')->once()->with($authorizer);
		}, $authorizer);

		$this->assertInstanceOf(User::class, $repository->getById(1));
	}

	/**
	 *
	 */
	public function testGetByAccessTokenWithAuthorizer(): void
	{
		/** @var AuthorizerInterface|Mockery\MockInterface $authorizer */
		$authorizer = Mockery::mock(AuthorizerInterface::class);

		$repository = $this->getRepository(function ($user) use ($authorizer): void {
			$user->shouldReceive('where')->once()->with('access_token', '=', 'foobar')->andReturn($user);

			$user->shouldReceive('first')->once()->andReturn($user);

			$user->shouldReceive('setAuthorizer')->once()->with($authorizer);
		}, $authorizer);

		$this->assertInstanceOf(User::class, $repository->getByAccessToken('foobar'));
	}

	/**
	 *
	 */
	public function testGetByActionTokenWithAuthorizer(): void
	{
		/** @var AuthorizerInterface|Mockery\MockInterface $authorizer */
		$authorizer = Mockery::mock(AuthorizerInterface::class);

		$repository = $this->getRepository(function ($user) use ($authorizer): void {
			$user->shouldReceive('where')->once()->with('action_token', '=', 'foobar')->andReturn($user);

			$user->shouldReceive('first')->once()->andReturn($user);

			$user->shouldReceive('setAuthorizer')->once()->with($authorizer);
		}, $authorizer);

		$this->assertInstanceOf(User::class, $repository->getByActionToken('foobar'));
	}
}
