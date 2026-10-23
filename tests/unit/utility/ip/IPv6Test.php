<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\utility\ip;

use mako\tests\TestCase;
use mako\utility\ip\IPv6;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
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

		// Non-network-aligned CIDR ranges

		$this->assertTrue(IPv6::inRange('2001:db8::1', '2001:db8::abcd/64'));
		$this->assertTrue(IPv6::inRange('2001:db8::abcd', '2001:db8::1/64'));
		$this->assertFalse(IPv6::inRange('2001:db9::1', '2001:db8::abcd/64'));

		// Zero-length netmask

		$this->assertTrue(IPv6::inRange('::1', '::/0'));
		$this->assertTrue(IPv6::inRange('2001:db8::1', 'fe80::1/0'));

		// Exact match with /128

		$this->assertTrue(IPv6::inRange('2001:db8::1', '2001:db8::1/128'));
		$this->assertFalse(IPv6::inRange('2001:db8::2', '2001:db8::1/128'));

		// /127 boundary with non-network-aligned range

		$this->assertTrue(IPv6::inRange('::0', '::1/127'));
		$this->assertTrue(IPv6::inRange('::1', '::1/127'));
		$this->assertFalse(IPv6::inRange('::2', '::1/127'));

		// Netmasks not divisible by 4

		$this->assertTrue(IPv6::inRange('2001:db8:0:0:8000::1', '2001:db8:0:0:8000::/65'));
		$this->assertFalse(IPv6::inRange('2001:db8:0:0:7fff::1', '2001:db8:0:0:8000::/65'));

		$this->assertTrue(IPv6::inRange('2001:db8:0:0:c000::1', '2001:db8:0:0:c000::/66'));
		$this->assertFalse(IPv6::inRange('2001:db8:0:0:8000::1', '2001:db8:0:0:c000::/66'));

		$this->assertTrue(IPv6::inRange('2001:db8:0:0:e000::1', '2001:db8:0:0:e000::/67'));
		$this->assertFalse(IPv6::inRange('2001:db8:0:0:dfff::1', '2001:db8:0:0:e000::/67'));

		// Invalid CIDR prefixes

		$this->assertFalse(IPv6::inRange('::1', '::/'));
		$this->assertFalse(IPv6::inRange('::1', '::/-1'));
		$this->assertFalse(IPv6::inRange('::1', '::/+64'));
		$this->assertFalse(IPv6::inRange('::1', '::/64abc'));
		$this->assertFalse(IPv6::inRange('::1', '::/64.5'));

		// IPv4 addresses must not be accepted

		$this->assertFalse(IPv6::inRange('127.0.0.1', '127.0.0.1'));
		$this->assertFalse(IPv6::inRange('127.0.0.1', '::1'));
		$this->assertFalse(IPv6::inRange('::1', '127.0.0.1'));
	}
}
