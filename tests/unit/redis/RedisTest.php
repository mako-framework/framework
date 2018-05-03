<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\redis;

use mako\redis\Redis;
use mako\tests\TestCase;
use Mockery;

/**
 * @group unit
 */
class RedisTest extends TestCase
{
	/**
	 *
	 */
	public function testAuth()
	{
		$connection = Mockery::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once()->with("*2\r\n$4\r\nAUTH\r\n$6\r\nfoobar\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("+OK\r\n");

		$redis = new Redis($connection, ['password' => 'foobar']);
	}

	/**
	 *
	 */
	public function testZeroDatabase()
	{
		$connection = Mockery::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->never()->with("*2\r\n$6\r\nSELECT\r\n$1\r\n0\r\n");

		$connection->shouldReceive('readLine')->never()->andReturn("+OK\r\n");

		$redis = new Redis($connection, ['database' => 0]);
	}

	/**
	 *
	 */
	public function testNonZeroDatabase()
	{
		$connection = Mockery::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once()->with("*2\r\n$6\r\nSELECT\r\n$1\r\n1\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("+OK\r\n");

		$redis = new Redis($connection, ['database' => 1]);
	}

	/**
	 *
	 */
	public function testMethodCall()
	{
		$connection = Mockery::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once()->with("*3\r\n$3\r\nSET\r\n$1\r\nx\r\n$1\r\n0\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("+OK\r\n");

		$redis = new Redis($connection);

		$redis->set('x', 0);
	}

	/**
	 *
	 */
	public function testMultiWordMethodCall()
	{
		$connection = Mockery::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once()->with("*2\r\n$6\r\nCONFIG\r\n$7\r\nREWRITE\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("+OK\r\n");

		$redis = new Redis($connection);

		$redis->configRewrite();
	}

	/**
	 *
	 */
	public function testDashSeparatedCommand()
	{
		$connection = Mockery::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once()->with("*2\r\n$7\r\nCLUSTER\r\n$16\r\nSET-CONFIG-EPOCH\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("+OK\r\n");

		$redis = new Redis($connection);

		$redis->clusterSetConfigEpoch();
	}

	/**
	 * @expectedException mako\redis\RedisException
	 */
	public function testException()
	{
		$connection = Mockery::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("-ERR unknown command 'foobar'\r\n");

		$redis = new Redis($connection);

		$redis->foobar();
	}

	/**
	 *
	 */
	public function testStatusReply()
	{
		$connection = Mockery::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("+OK\r\n");

		$redis = new Redis($connection);

		$this->assertSame('OK', $redis->foobar());
	}

	/**
	 *
	 */
	public function testIntegerReply()
	{
		$connection = Mockery::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn(":1\r\n");

		$redis = new Redis($connection);

		$this->assertSame(1, $redis->foobar());
	}

	/**
	 *
	 */
	public function testBulkReply()
	{
		$connection = Mockery::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("$6\r\n");

		$connection->shouldReceive('read')->once()->andReturn("foobar\r\n");

		$redis = new Redis($connection);

		$this->assertSame('foobar', $redis->foobar());
	}

	/**
	 *
	 */
	public function testBulkNullReply()
	{
		$connection = Mockery::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("$-1\r\n");

		$redis = new Redis($connection);

		$this->assertSame(null, $redis->foobar());
	}

	/**
	 *
	 */
	public function testMultiBulkReply()
	{
		$connection = Mockery::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("*2\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("$3\r\n");

		$connection->shouldReceive('read')->once()->andReturn("foo\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("$3\r\n");

		$connection->shouldReceive('read')->once()->andReturn("bar\r\n");

		$redis = new Redis($connection);

		$this->assertSame(['foo', 'bar'], $redis->foobar());
	}

	/**
	 *
	 */
	public function testMultiBulkMixedReply()
	{
		$connection = Mockery::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("*2\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn(":3\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("$3\r\n");

		$connection->shouldReceive('read')->once()->andReturn("bar\r\n");

		$redis = new Redis($connection);

		$this->assertSame([3, 'bar'], $redis->foobar());
	}

	/**
	 *
	 */
	public function testMultiBulkEmptyReply()
	{
		$connection = Mockery::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("*0\r\n");

		$redis = new Redis($connection);

		$this->assertSame([], $redis->foobar());
	}

	/**
	 *
	 */
	public function testMultiBulkNullReply()
	{
		$connection = Mockery::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("*-1\r\n");

		$redis = new Redis($connection);

		$this->assertSame(null, $redis->foobar());
	}

	/**
	 * @expectedException \mako\redis\RedisException
	 * @expectedExcetionMessage \mako\redis\Redis::response(): Unable to handle server response.
	 */
	public function testInvalidResponse()
	{
		$connection = Mockery::mock('mako\redis\Connection');

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn('foobar');

		$redis = new Redis($connection);

		$redis->foobar();
	}
}
