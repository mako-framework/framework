<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules\session;

use mako\session\Session;
use mako\tests\TestCase;
use mako\validator\rules\session\Token;
use Mockery;

/**
 * @group unit
 */
class TokenTest extends TestCase
{
	/**
	 *
	 */
	public function testWithValidValue()
	{
		$session = Mockery::mock(Session::class);

		$session->shouldReceive('validateToken')->once()->with('foobar')->andReturnTrue();

		$rule = new Token($session);

		$this->assertTrue($rule->validate('foobar', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue()
	{
		$session = Mockery::mock(Session::class);

		$session->shouldReceive('validateToken')->once()->with('foobar')->andReturnFalse();

		$rule = new Token($session);

		$this->assertFalse($rule->validate('foobar', []));

		$this->assertSame('Invalid security token.', $rule->getErrorMessage('foobar'));
	}
}
