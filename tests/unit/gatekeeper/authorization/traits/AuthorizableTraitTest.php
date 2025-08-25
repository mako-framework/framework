<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\gatekeeper\authorization\traits;

use mako\gatekeeper\authorization\AuthorizableInterface;
use mako\gatekeeper\authorization\AuthorizerInterface;
use mako\gatekeeper\authorization\traits\AuthorizableTrait;
use mako\gatekeeper\entities\user\User;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class AuthorizableTraitTest extends TestCase
{
	/**
	 *
	 */
	public function testThatTheTraitImplementsTheAuthorizableInterface(): void
	{
		$authorizable = new class implements AuthorizableInterface {
			use AuthorizableTrait;
		};

		$this->assertInstanceOf(AuthorizableInterface::class, $authorizable);
	}

	/**
	 *
	 */
	public function testSetAuthorizerAndCan(): void
	{
		$authorizable = new User;

		$authorizer = Mockery::mock(AuthorizerInterface::class);

		$authorizer->shouldReceive('can')->with($authorizable, 'action', 'entity')->andReturn(true);

		$authorizable->setAuthorizer($authorizer);

		$this->assertTrue($authorizable->can('action', 'entity'));
	}

	/**
	 *
	 */
	public function testSetAuthorizerAndCanWithAdditionalParams(): void
	{
		$authorizable = new User;

		$authorizer = Mockery::mock(AuthorizerInterface::class);

		$authorizer->shouldReceive('can')->with($authorizable, 'action', 'entity', 'one', 'two', 'three')->andReturn(true);

		$authorizable->setAuthorizer($authorizer);

		$this->assertTrue($authorizable->can('action', 'entity', 'one', 'two', 'three'));
	}
}
