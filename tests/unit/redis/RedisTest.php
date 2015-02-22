<?php

namespace mako\tests\unit\redis;

use mako\redis\Redis;

use Mockery as m;

use PHPUnit_Framework_TestCase;

/**
 * @group unit
 */

class RedisTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function tearDown()
	{
		m::close();
	}

	/**
	 *
	 */

	public function testAuth()
	{
		$connection = m::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once()->with("*2\r\n$4\r\nAUTH\r\n$6\r\nfoobar\r\n");

		$connection->shouldReceive('gets')->once()->andReturn("+OK\r\n");

		$redis = new Redis($connection, ['password' => 'foobar']);
	}

	/**
	 *
	 */

	public function testZeroDatabase()
	{
		$connection = m::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->never()->with("*2\r\n$6\r\nSELECT\r\n$1\r\n0\r\n");

		$connection->shouldReceive('gets')->never()->andReturn("+OK\r\n");

		$redis = new Redis($connection, ['database' => 0]);
	}

	/**
	 *
	 */

	public function testNonZeroDatabase()
	{
		$connection = m::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once()->with("*2\r\n$6\r\nSELECT\r\n$1\r\n1\r\n");

		$connection->shouldReceive('gets')->once()->andReturn("+OK\r\n");

		$redis = new Redis($connection, ['database' => 1]);
	}

	/**
	 *
	 */

	public function testMethodCall()
	{
		$connection = m::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once()->with("*3\r\n$3\r\nSET\r\n$1\r\nx\r\n$1\r\n0\r\n");

		$connection->shouldReceive('gets')->once()->andReturn("+OK\r\n");

		$redis = new Redis($connection);

		$redis->set('x', 0);
	}

	/**
	 *
	 */

	public function testMultiWordMethodCall()
	{
		$connection = m::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once()->with("*2\r\n$6\r\nCONFIG\r\n$7\r\nREWRITE\r\n");

		$connection->shouldReceive('gets')->once()->andReturn("+OK\r\n");

		$redis = new Redis($connection);

		$redis->configRewrite();
	}

	/**
	 * @expectedException mako\redis\RedisException
	 */

	public function testException()
	{
		$connection = m::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('gets')->once()->andReturn("-ERR unknown command 'foobar'\r\n");

		$redis = new Redis($connection);

		$redis->foobar();
	}

	/**
	 *
	 */

	public function testStatusReply()
	{
		$connection = m::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('gets')->once()->andReturn("+OK\r\n");

		$redis = new Redis($connection);

		$this->assertSame('OK', $redis->foobar());
	}

	/**
	 *
	 */

	public function testIntegerReply()
	{
		$connection = m::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('gets')->once()->andReturn(":1\r\n");

		$redis = new Redis($connection);

		$this->assertSame(1, $redis->foobar());
	}

	/**
	 *
	 */

	public function testBulkReply()
	{
		$connection = m::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('gets')->once()->andReturn("$6\r\n");

		$connection->shouldReceive('read')->once()->andReturn("foobar\r\n");

		$redis = new Redis($connection);

		$this->assertSame('foobar', $redis->foobar());
	}

	/**
	 *
	 */

	public function testBulkNullReply()
	{
		$connection = m::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('gets')->once()->andReturn("$-1\r\n");

		$redis = new Redis($connection);

		$this->assertSame(null, $redis->foobar());
	}

	/**
	 *
	 */

	public function testMultiBulkReply()
	{
		$connection = m::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('gets')->once()->andReturn("*2\r\n");

		$connection->shouldReceive('gets')->once()->andReturn("$3\r\n");

		$connection->shouldReceive('read')->once()->andReturn("foo\r\n");

		$connection->shouldReceive('gets')->once()->andReturn("$3\r\n");

		$connection->shouldReceive('read')->once()->andReturn("bar\r\n");

		$redis = new Redis($connection);

		$this->assertSame(['foo', 'bar'], $redis->foobar());
	}

	/**
	 *
	 */

	public function testMultiBulkMixedReply()
	{
		$connection = m::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('gets')->once()->andReturn("*2\r\n");

		$connection->shouldReceive('gets')->once()->andReturn(":3\r\n");

		$connection->shouldReceive('gets')->once()->andReturn("$3\r\n");

		$connection->shouldReceive('read')->once()->andReturn("bar\r\n");

		$redis = new Redis($connection);

		$this->assertSame([3, 'bar'], $redis->foobar());
	}

	/**
	 *
	 */

	public function testMultiBulkEmptyReply()
	{
		$connection = m::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('gets')->once()->andReturn("*0\r\n");

		$redis = new Redis($connection);

		$this->assertSame([], $redis->foobar());
	}

	/**
	 *
	 */

	public function testMultiBulkNullReply()
	{
		$connection = m::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('gets')->once()->andReturn("*-1\r\n");

		$redis = new Redis($connection);

		$this->assertSame(null, $redis->foobar());
	}

	/**
	 * @expectedException \mako\redis\RedisException
	 * @expectedExcetionMessage \mako\redis\Redis::response(): Unable to handle server response.
	 */

	public function testInvalidResponse()
	{
		$connection = m::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('gets')->once()->andReturn("foobar");

		$redis = new Redis($connection);

		$redis->foobar();
	}
}