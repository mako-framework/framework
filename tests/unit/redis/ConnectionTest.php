<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\redis;

use mako\redis\Connection;
use mako\redis\exceptions\RedisException;
use mako\tests\TestCase;

/**
 * @group unit
 */
class ConnectionTest extends TestCase
{
	/**
	 *
	 */
	public function testFailedConnection(): void
	{
		$this->expectException(RedisException::class);

		$this->expectExceptionMessageMatches('/^Failed to connect to \[ foobar.nope:7777 \]\./');

		new Connection('foobar.nope', 7777);
	}

	/**
	 *
	 */
	public function testFailedConnectionWithName(): void
	{
		$this->expectException(RedisException::class);

		$this->expectExceptionMessageMatches('/^Failed to connect to \[ test \]\./');

		new Connection('foobar.nope', 7777, ['name' => 'test']);
	}
}
