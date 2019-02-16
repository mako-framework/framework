<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use DateTimeZone;

use mako\tests\TestCase;
use mako\validator\rules\TimeZone;

/**
 * @group unit
 */
class TimeZoneTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new TimeZone;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new TimeZone;

		$this->assertTrue($rule->validate('Europe/Oslo', []));
	}

	/**
	 *
	 */
	public function testWithGroupAndValidValue(): void
	{
		$rule = new TimeZone(DateTimeZone::EUROPE);

		$this->assertTrue($rule->validate('Europe/Oslo', []));
	}

	/**
	 *
	 */
	public function testWithGroupCountryAndValidValue(): void
	{
		$rule = new TimeZone(DateTimeZone::PER_COUNTRY, 'NO');

		$this->assertTrue($rule->validate('Europe/Oslo', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new TimeZone;

		$this->assertFalse($rule->validate('Foo/bar', []));

		$this->assertSame('The foobar field must contain a valid time zone.', $rule->getErrorMessage('foobar'));
	}

	/**
	 *
	 */
	public function testWithGroupAndInvalidValue(): void
	{
		$rule = new TimeZone(DateTimeZone::AMERICA);

		$this->assertFalse($rule->validate('Europe/Oslo', []));

		$this->assertSame('The foobar field must contain a valid time zone.', $rule->getErrorMessage('foobar'));
	}

	/**
	 *
	 */
	public function testWithGroupCountryAndInvalidValue(): void
	{
		$rule = new TimeZone(DateTimeZone::PER_COUNTRY, 'NO');

		$this->assertFalse($rule->validate('Europe/Paris', []));

		$this->assertSame('The foobar field must contain a valid time zone.', $rule->getErrorMessage('foobar'));
	}
}
