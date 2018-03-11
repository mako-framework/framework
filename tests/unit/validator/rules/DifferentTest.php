<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\Different;

/**
 * @group unit
 */
class DifferentTest extends TestCase
{
	/**
	 *
	 */
	public function testWithValidValue()
	{
		$rule = new Different;

		$rule->setParameters(['barfoo']);

		$this->assertTrue($rule->validate('foo', ['barfoo' => 'bar']));
	}

	/**
	 *
	 */
	public function testWithInvalidValue()
	{
		$rule = new Different;

		$rule->setParameters(['barfoo']);

		$this->assertFalse($rule->validate('bar', ['barfoo' => 'bar']));

		$this->assertSame('The values of the foobar field and barfoo field must be different.', $rule->getErrorMessage('foobar'));
	}
}
