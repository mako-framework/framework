<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\Regex;

/**
 * @group unit
 */
class RegexTest extends TestCase
{
	/**
	 *
	 */
	public function testWithValidValue()
	{
		$rule = new Regex;

		$rule->setParameters(['/^[a-z]+$/i']);

		$this->assertTrue($rule->validate('foobar', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue()
	{
		$rule = new Regex;

		$rule->setParameters(['/^[a-z]+$/i']);

		$this->assertFalse($rule->validate('foobar1', []));

		$this->assertSame('The value of the foobar field does not match the required format.', $rule->getErrorMessage('foobar'));
	}
}
