<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\gatekeeper\authorization\http\routing\traits;

use mako\gatekeeper\authorization\AuthorizerInterface;
use mako\gatekeeper\authorization\http\routing\traits\AuthorizationTrait;
use mako\gatekeeper\Gatekeeper;
use mako\http\exceptions\ForbiddenException;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class AuthorizationTraitTest extends TestCase
{
	/**
	 *
	 */
	public function testSuccess(): void
	{
		$class = new class {
			use AuthorizationTrait;

			public $gatekeeper;
			public $authorizer;

			public function test(): void
			{
				$this->authorize('action', 'entity');
			}
		};

		$class->gatekeeper = Mockery::mock(Gatekeeper::class);

		$class->gatekeeper->shouldReceive('getUser')->once()->andReturn(null);

		$class->authorizer = Mockery::mock(AuthorizerInterface::class);

		$class->authorizer->shouldReceive('can')->once()->with(null, 'action', 'entity')->andReturn(true);

		$class->test();
	}

	/**
	 *
	 */
	public function testSuccessWithAdditionalParameters(): void
	{
		$class = new class {
			use AuthorizationTrait;

			public $gatekeeper;
			public $authorizer;

			public function test(): void
			{
				$this->authorize('action', 'entity', 'one', 'two', 'three');
			}
		};

		$class->gatekeeper = Mockery::mock(Gatekeeper::class);

		$class->gatekeeper->shouldReceive('getUser')->once()->andReturn(null);

		$class->authorizer = Mockery::mock(AuthorizerInterface::class);

		$class->authorizer->shouldReceive('can')->once()->with(null, 'action', 'entity', 'one', 'two', 'three')->andReturn(true);

		$class->test();
	}

	/**
	 *
	 */
	public function testFailure(): void
	{
		$this->expectException(ForbiddenException::class);

		$class = new class {
			use AuthorizationTrait;

			public $gatekeeper;
			public $authorizer;

			public function test(): void
			{
				$this->authorize('action', 'entity');
			}
		};

		$class->gatekeeper = Mockery::mock(Gatekeeper::class);

		$class->gatekeeper->shouldReceive('getUser')->once()->andReturn(null);

		$class->authorizer = Mockery::mock(AuthorizerInterface::class);

		$class->authorizer->shouldReceive('can')->once()->with(null, 'action', 'entity')->andReturn(false);

		$class->test();
	}
}
