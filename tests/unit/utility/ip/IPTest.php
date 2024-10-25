<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\utility\ip;

use mako\tests\TestCase;
use mako\utility\ip\IP;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class IPTest extends TestCase
{
	/**
	 *
	 */
	public function testInRange(): void
	{
		$this->assertTrue(IP::inRange('127.0.0.40', '127.0.0.0/24'));
		$this->assertFalse(IP::inRange('127.0.0.1', '127.0.0.0/32'));

		$this->assertTrue(IP::inRange('::1', '::0/127'));
		$this->assertFalse(IP::inRange('::1', '::0/128'));
	}
}
