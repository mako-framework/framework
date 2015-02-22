<?php

namespace mako\tests\unit\redis;

use mako\redis\Connection;

use PHPUnit_Framework_TestCase;

/**
 * @group unit
 */

class ConnectionTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @expectedException \mako\redis\RedisException
	 */

	public function testFailedConnection()
	{
		$connection = new Connection('foobar.nope', 7777);
	}
}