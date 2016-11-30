<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\unit\utility\ip;

use PHPUnit_Framework_TestCase;

use mako\utility\ip\IPv4;

/**
 * @group unit
 */
class IPv4Test extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function testInRange()
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
	}
}
