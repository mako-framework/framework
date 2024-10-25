<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules\session;

use mako\session\Session;
use mako\tests\TestCase;
use mako\validator\rules\session\OneTimeToken;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class OneTimeTokenTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		/** @var \mako\session\Session|\Mockery\MockInterface $session */
		$session = Mockery::mock(Session::class);

		$rule = new OneTimeToken($session);

		$this->assertTrue($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		/** @var \mako\session\Session|\Mockery\MockInterface $session */
		$session = Mockery::mock(Session::class);

		$session->shouldReceive('validateOneTimeToken')->once()->with('foobar')->andReturnTrue();

		$rule = new OneTimeToken($session);

		$this->assertTrue($rule->validate('foobar', '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		/** @var \mako\session\Session|\Mockery\MockInterface $session */
		$session = Mockery::mock(Session::class);

		$session->shouldReceive('validateOneTimeToken')->once()->with('foobar')->andReturnFalse();

		$rule = new OneTimeToken($session);

		$this->assertFalse($rule->validate('foobar', '', []));

		$this->assertSame('Invalid security token.', $rule->getErrorMessage('foobar'));
	}
}
