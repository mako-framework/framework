<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\gatekeeper\authorization;

use mako\gatekeeper\authorization\Authorizer;
use mako\gatekeeper\authorization\exceptions\AuthorizerException;
use mako\gatekeeper\authorization\policies\Policy;
use mako\gatekeeper\authorization\policies\PolicyInterface;
use mako\gatekeeper\entities\user\UserEntityInterface;
use mako\syringe\Container;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class TestEntity
{

}

class BeforeTruePolicy extends Policy
{
	public function before(?UserEntityInterface $user, string $action, $entity): ?bool
	{
		return true;
	}
}

class BeforeFalsePolicy extends Policy
{
	public function before(?UserEntityInterface $user, string $action, $entity): ?bool
	{
		return false;
	}
}

class BeforeTruePolicyWithAdditionalParameters extends Policy
{
	public function before(?UserEntityInterface $user, string $action, $entity, ...$parameters): ?bool
	{
		return $parameters === [1, 2, 3];
	}
}

class BeforeNullPolicy extends Policy
{

}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

#[Group('unit')]
class AuthorizerTest extends TestCase
{
	/**
	 *
	 */
	public function testBeforeTrue(): void
	{
		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->with(BeforeTruePolicy::class)->once()->andReturn(new BeforeTruePolicy);

		$authorizer = new Authorizer($container);

		$authorizer->registerPolicy(TestEntity::class, BeforeTruePolicy::class);

		$this->assertTrue($authorizer->can(null, 'update', TestEntity::class));
	}

	/**
	 *
	 */
	public function testBeforeFalse(): void
	{
		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->with(BeforeFalsePolicy::class)->once()->andReturn(new BeforeFalsePolicy);

		$authorizer = new Authorizer($container);

		$authorizer->registerPolicy(TestEntity::class, BeforeFalsePolicy::class);

		$this->assertFalse($authorizer->can(null, 'update', TestEntity::class));
	}

	/**
	 *
	 */
	public function testBeforeNullWithTrue(): void
	{
		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->with(BeforeNullPolicy::class)->once()->andReturn(new class extends BeforeNullPolicy {
			public function update(): bool
			{
				return true;
			}
		});

		$authorizer = new Authorizer($container);

		$authorizer->registerPolicy(TestEntity::class, BeforeNullPolicy::class);

		$this->assertTrue($authorizer->can(null, 'update', TestEntity::class));
	}

	/**
	 *
	 */
	public function testBeforeNullWithFalse(): void
	{
		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->with(BeforeNullPolicy::class)->once()->andReturn(new class extends BeforeNullPolicy {
			public function update(): bool
			{
				return false;
			}
		});

		$authorizer = new Authorizer($container);

		$authorizer->registerPolicy(TestEntity::class, BeforeNullPolicy::class);

		$this->assertFalse($authorizer->can(null, 'update', TestEntity::class));
	}

	/**
	 *
	 */
	public function testBeforeNullWithAdditionalParametersAndTrue(): void
	{
		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->with(BeforeNullPolicy::class)->once()->andReturn(new class extends BeforeNullPolicy {
			public function update($user, $entity, ...$params): bool
			{
				return $params === [1, 2, 3];
			}
		});

		$authorizer = new Authorizer($container);

		$authorizer->registerPolicy(TestEntity::class, BeforeNullPolicy::class);

		$this->assertTrue($authorizer->can(null, 'update', TestEntity::class, 1, 2, 3));
	}

	/**
	 *
	 */
	public function testThatBeforeGetsTheExpectedParameters(): void
	{
		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->with(BeforeTruePolicy::class)->once()->andReturn(new class implements PolicyInterface {
			public function before(?UserEntityInterface $user, string $action, $entity): ?bool
			{
				return $user === null && $action === 'update' && $entity === TestEntity::class;
			}
		});

		$authorizer = new Authorizer($container);

		$authorizer->registerPolicy(TestEntity::class, BeforeTruePolicy::class);

		$this->assertTrue($authorizer->can(null, 'update', TestEntity::class));

		//

		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->with(BeforeTruePolicy::class)->once()->andReturn(new class implements PolicyInterface {
			public function before(?UserEntityInterface $user, string $action, $entity): ?bool
			{
				return count(func_get_args()) === 3 && $user === null && $action === 'update' && $entity === TestEntity::class;
			}
		});

		$authorizer = new Authorizer($container);

		$authorizer->registerPolicy(TestEntity::class, BeforeTruePolicy::class);

		$this->assertTrue($authorizer->can(null, 'update', TestEntity::class, 1, 2, 3));
	}

	/**
	 *
	 */
	public function testThatUpdateGetsTheExpectedParameters(): void
	{
		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->with(BeforeNullPolicy::class)->once()->andReturn(new class extends BeforeNullPolicy {
			public function update($user, $entity): bool
			{
				return $user === null && $entity === TestEntity::class;
			}
		});

		$authorizer = new Authorizer($container);

		$authorizer->registerPolicy(TestEntity::class, BeforeNullPolicy::class);

		$this->assertTrue($authorizer->can(null, 'update', TestEntity::class));

		//

		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->with(BeforeNullPolicy::class)->once()->andReturn(new class extends BeforeNullPolicy {
			public function update($user, $entity, ...$parameters): bool
			{
				return $user === null && $entity === TestEntity::class && $parameters === [1, 2, 3];
			}
		});

		$authorizer = new Authorizer($container);

		$authorizer->registerPolicy(TestEntity::class, BeforeNullPolicy::class);

		$this->assertTrue($authorizer->can(null, 'update', TestEntity::class, 1, 2, 3));

		//

		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->with(BeforeNullPolicy::class)->once()->andReturn(new class extends BeforeNullPolicy {
			public function update($user, $entity, $one, $two, $three): bool
			{
				return $user === null && $entity === TestEntity::class && $one === 1 && $two === 2 && $three === 3;
			}
		});

		$authorizer = new Authorizer($container);

		$authorizer->registerPolicy(TestEntity::class, BeforeNullPolicy::class);

		$this->assertTrue($authorizer->can(null, 'update', TestEntity::class, 1, 2, 3));
	}

	/**
	 *
	 */
	public function testAuthorizationWithoutPolicy(): void
	{
		$this->expectException(AuthorizerException::class);

		$this->expectExceptionMessage('There is no authorization policy registered for [ mako\tests\unit\gatekeeper\authorization\TestEntity ] entities.');

		$container = Mockery::mock(Container::class);

		$authorizer = new Authorizer($container);

		$this->assertTrue($authorizer->can(null, 'update', TestEntity::class));
	}
}
