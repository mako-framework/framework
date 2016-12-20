<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\redis;

use PHPUnit_Framework_TestCase;

use mako\redis\Connection;

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
