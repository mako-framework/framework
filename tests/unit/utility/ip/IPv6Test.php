<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\utility\ip;

use mako\tests\TestCase;
use mako\utility\ip\IPv6;

/**
 * @group unit
 */
class IPv6Test extends TestCase
{
	/**
	 *
	 */
	public function testInRange(): void
	{
		$this->assertTrue(IPv6::inRange('::1', '::1'));
		$this->assertTrue(IPv6::inRange('0:0:0:0:0:0:0:1', '::1'));
		$this->assertTrue(IPv6::inRange('::1', '::0/1'));
		$this->assertTrue(IPv6::inRange('::1', '::0/127'));

		$this->assertFalse(IPv6::inRange('::2', '::1'));
		$this->assertFalse(IPv6::inRange('0:0:0:0:0:0:0:2', '::1'));
		$this->assertFalse(IPv6::inRange('::1', '::0/128'));
		$this->assertFalse(IPv6::inRange('::3', '::0/127'));

		$this->assertFalse(IPv6::inRange('::1', '::1/129')); // Invalid CIDR
		$this->assertFalse(IPv6::inRange('::x', '::1'));     // Invalid IP
		$this->assertFalse(IPv6::inRange('::1', '::x'));     // Invalid Range
		$this->assertFalse(IPv6::inRange('::x', '::x'));     // Invalid IP and Range
	}
}
