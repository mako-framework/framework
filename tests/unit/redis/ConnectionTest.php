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
	 * @expectedExceptionMessageRegExp /^Failed to connect\./
	 */
	public function testFailedConnection(): void
	{
		$connection = new Connection('foobar.nope', 7777);
	}

	/**
	 * @expectedException \mako\redis\RedisException
	 * @expectedExceptionMessageRegExp /^Failed to connect to \[ test \]\./
	 */
	public function testFailedConnectionWithName(): void
	{
		$connection = new Connection('foobar.nope', 7777, false, 60, 'test');
	}
}
