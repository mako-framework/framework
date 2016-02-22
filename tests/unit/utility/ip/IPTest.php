<?php

namespace mako\tests\unit\utility\ip;

use mako\utility\ip\IP;

/**
 * @group unit
 */
class IPTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function testInRange()
	{

		$this->assertTrue(IP::inRange('127.0.0.40', '127.0.0.0/24'));
		$this->assertFalse(IP::inRange('127.0.0.1', '127.0.0.0/32'));

		$this->assertTrue(IP::inRange('::1', '::0/127'));
		$this->assertFalse(IP::inRange('::1', '::0/128'));
	}
}