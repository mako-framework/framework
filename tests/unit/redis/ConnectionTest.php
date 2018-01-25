<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\redis;

use mako\redis\Connection;
use mako\tests\TestCase;

/**
 * @group unit
 */
class ConnectionTest extends TestCase
{
	/**
	 * @expectedException \mako\redis\RedisException
	 */
	public function testFailedConnection()
	{
		$connection = new Connection('foobar.nope', 7777);
	}
}
