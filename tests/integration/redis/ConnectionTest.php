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
			$this->connection = new Connection('localhost', 6379,
			[
				'name'               => 'test',
				'persistent'         => false,
				'connection_timeout' => 4,
				'read_write_timeout' => 59,
				'nodelay'            => false,
			]);
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
	public function testGeOptions(): void
	{
		$options = $this->connection->getOptions();

		$this->assertSame('test', $options['name']);

		$this->assertFalse($options['persistent']);

		$this->assertSame(4, $options['connection_timeout']);

		$this->assertSame(59, $options['read_write_timeout']);

		$this->assertFalse($options['nodelay']);
	}
}