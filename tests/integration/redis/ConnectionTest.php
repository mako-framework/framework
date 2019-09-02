<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\integration\redis;

use mako\redis\Connection;
use mako\redis\RedisException;
use mako\tests\TestCase;

/**
 * @group integration
 * @group integration:redis
 */
class ConnectionTest extends TestCase
{
	/**
	 * @var \mako\redis\Connection.
	 */
	protected $connection;

	/**
	 *
	 */
	public function setUp(): void
	{
		try
		{
			$this->connection = new Connection('localhost', 6379, false, 60, 5, 'test');
		}
		catch(RedisException $e)
		{
			$this->markTestSkipped('Unable to connect to redis server.');
		}
	}

	/**
	 *
	 */
	public function tearDown(): void
	{
		if($this->connection !== null)
		{
			$this->connection = null;
		}
	}

	/**
	 *
	 */
	public function testGetReadWriteTimeout(): void
	{
		$this->assertSame(60, $this->connection->getReadWriteTimeout());
	}

	/**
	 *
	 */
	public function testGetConnectionTimeout(): void
	{
		$this->assertSame(5, $this->connection->getConnectionTimeout());
	}

	/**
	 *
	 */
	public function testIsPersistent(): void
	{
		$this->assertFalse($this->connection->isPersistent());
	}

	/**
	 *
	 */
	public function testGetName(): void
	{
		$this->assertSame('test', $this->connection->getName());
	}
}
