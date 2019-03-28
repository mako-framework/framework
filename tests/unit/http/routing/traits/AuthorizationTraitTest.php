<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\routing\traits;

use mako\gatekeeper\authorization\AuthorizerInterface;
use mako\gatekeeper\Gatekeeper;
use mako\http\exceptions\ForbiddenException;
use mako\http\routing\traits\AuthorizationTrait;
use mako\tests\TestCase;
use Mockery;

/**
 * @group unit
 */
class AuthorizationTraitTest extends TestCase
{
	/**
	 *
	 */
	public function testWithSuccess(): void
	{
		$class = new class
		{
			use AuthorizationTrait;

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
	public function testWithFailure(): void
	{
		$this->expectException(ForbiddenException::class);

		$class = new class
		{
			use AuthorizationTrait;

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
