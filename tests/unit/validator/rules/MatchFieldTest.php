<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\MatchField;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class MatchFieldTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new MatchField('barfoo');

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new MatchField('barfoo');

		$this->assertTrue($rule->validate('bar', '', ['barfoo' => 'bar']));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new MatchField('barfoo');

		$this->assertFalse($rule->validate('foo', '', ['barfoo' => 'bar']));

		$this->assertSame('The values of the foobar field and barfoo field must match.', $rule->getErrorMessage('foobar'));
	}
}
