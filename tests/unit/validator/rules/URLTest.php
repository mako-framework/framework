<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\URL;

/**
 * @group unit
 */
class URLTest extends TestCase
{
	/**
	 *
	 */
	public function testWithValidValue()
	{
		$rule = new URL;

		$this->assertTrue($rule->validate('http://foobar', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue()
	{
		$rule = new URL;

		$this->assertFalse($rule->validate('foobar', []));

		$this->assertSame('The foobar field must contain a valid URL.', $rule->getErrorMessage('foobar'));
	}
}
