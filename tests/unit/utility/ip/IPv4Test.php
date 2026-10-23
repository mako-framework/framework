<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\utility\ip;

use mako\tests\TestCase;
use mako\utility\ip\IPv4;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class IPv4Test extends TestCase
{
	/**
	 *
	 */
	public function testInRange(): void
	{
		$this->assertTrue(IPv4::inRange('127.0.0.1', '127.0.0.1'));
		$this->assertTrue(IPv4::inRange('127.0.0.1', '127.0.0.0/1'));
		$this->assertTrue(IPv4::inRange('127.0.0.40', '127.0.0.0/24'));

		$this->assertFalse(IPv4::inRange('127.0.0.2', '127.0.0.1'));
		$this->assertFalse(IPv4::inRange('127.0.0.1', '127.0.0.0/32'));
		$this->assertFalse(IPv4::inRange('127.0.0.40', '127.0.0.0/27'));

		$this->assertFalse(IPv4::inRange('127.0.0.1', '127.0.0.0/33')); // Invalid CIDR
		$this->assertFalse(IPv4::inRange('xxx.x.x.x', '127.0.0.1'));    // Invalid IP
		$this->assertFalse(IPv4::inRange('127.0.0.1', 'xxx.x.x.x'));    // Invalid Range
		$this->assertFalse(IPv4::inRange('xxx.x.x.x', 'xxx.x.x.x'));    // Invalid IP and Range

		// Non-network-aligned CIDR ranges

		$this->assertTrue(IPv4::inRange('192.168.1.10', '192.168.1.100/24'));
		$this->assertFalse(IPv4::inRange('192.168.2.10', '192.168.1.100/24'));

		// Zero-length netmask

		$this->assertTrue(IPv4::inRange('8.8.8.8', '0.0.0.0/0'));
		$this->assertTrue(IPv4::inRange('255.255.255.255', '192.168.1.1/0'));

		// Exact match with /32

		$this->assertTrue(IPv4::inRange('192.168.1.1', '192.168.1.1/32'));
		$this->assertFalse(IPv4::inRange('192.168.1.2', '192.168.1.1/32'));

		// /31 boundary

		$this->assertTrue(IPv4::inRange('192.168.1.8', '192.168.1.8/31'));
		$this->assertTrue(IPv4::inRange('192.168.1.9', '192.168.1.8/31'));
		$this->assertFalse(IPv4::inRange('192.168.1.10', '192.168.1.8/31'));

		// Invalid CIDR prefixes

		$this->assertFalse(IPv4::inRange('127.0.0.1', '127.0.0.0/'));
		$this->assertFalse(IPv4::inRange('127.0.0.1', '127.0.0.0/-1'));
		$this->assertFalse(IPv4::inRange('127.0.0.1', '127.0.0.0/+24'));
		$this->assertFalse(IPv4::inRange('127.0.0.1', '127.0.0.0/24abc'));
		$this->assertFalse(IPv4::inRange('127.0.0.1', '127.0.0.0/24.5'));

		// IPv6 addresses must not be accepted

		$this->assertFalse(IPv4::inRange('::1', '127.0.0.1'));
		$this->assertFalse(IPv4::inRange('127.0.0.1', '::1'));
	}
}
