<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\EmailDomain;
use phpmock\MockBuilder;

/**
 * @group unit
 */
class EmailDomainTest extends TestCase
{
	/**
	 *
	 */
	public function testWithValidValue()
	{
		$checkdnsrr = (new MockBuilder)
		->setNamespace('mako\validator\rules')
		->setName('checkdnsrr')
		->setFunction(function($host, $type)
		{
			$this->assertSame('example.org', $host);
			$this->assertSame('MX', $type);

			return true;
		})
		->build();

		$checkdnsrr->enable();

		$rule = new EmailDomain;

		$this->assertTrue($rule->validate('foo@example.org', []));

		$checkdnsrr->disable();
	}

	/**
	 *
	 */
	public function testWithInvalidValue()
	{
		$checkdnsrr = (new MockBuilder)
		->setNamespace('mako\validator\rules')
		->setName('checkdnsrr')
		->setFunction(function($host, $type)
		{
			$this->assertSame('example.org', $host);
			$this->assertSame('MX', $type);

			return false;
		})
		->build();

		$checkdnsrr->enable();

		$rule = new EmailDomain;

		$this->assertFalse($rule->validate('foo@example.org', []));

		$this->assertSame('The foobar field must contain a valid e-mail address.', $rule->getErrorMessage('foobar'));

		$checkdnsrr->disable();
	}
}
