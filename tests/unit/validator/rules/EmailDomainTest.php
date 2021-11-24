<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\EmailDomain;
use Mockery;

/**
 * @group unit
 */
class EmailDomainTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new EmailDomain;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = Mockery::mock(EmailDomain::class)->shouldAllowMockingProtectedMethods();

		$rule->makePartial();

		$rule->shouldReceive('hasMXRecord')->once()->with('example.org')->andReturn(true);

		$this->assertTrue($rule->validate('foo@example.org', '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = Mockery::mock(EmailDomain::class)->shouldAllowMockingProtectedMethods();

		$rule->makePartial();

		$rule->shouldReceive('hasMXRecord')->once()->with('example.org')->andReturn(false);

		$this->assertFalse($rule->validate('foo@example.org', '', []));

		$this->assertSame('The foobar field must contain a valid e-mail address.', $rule->getErrorMessage('foobar'));
	}
}
