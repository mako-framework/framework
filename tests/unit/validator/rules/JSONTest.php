<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\JSON;

/**
 * @group unit
 */
class JSONTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty()
	{
		$rule = new JSON;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue()
	{
		$rule = new JSON;

		$this->assertTrue($rule->validate('"hello"', []));
		$this->assertTrue($rule->validate('false', []));
		$this->assertTrue($rule->validate('{"hello":"world"}', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue()
	{
		$rule = new JSON;

		$this->assertFalse($rule->validate('{hello:"world"}', []));

		$this->assertSame('The foobar field must contain valid JSON.', $rule->getErrorMessage('foobar'));
	}
}
