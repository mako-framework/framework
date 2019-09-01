<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\redis;

use mako\redis\Connection;
use mako\redis\Redis;
use mako\redis\RedisException;
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
	public function testAuth(): void
	{
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once()->with("*2\r\n$4\r\nAUTH\r\n$6\r\nfoobar\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("+OK\r\n");

		new Redis($connection, ['password' => 'foobar']);
	}

	/**
	 *
	 */
	public function testZeroDatabase(): void
	{
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->never()->with("*2\r\n$6\r\nSELECT\r\n$1\r\n0\r\n");

		$connection->shouldReceive('readLine')->never()->andReturn("+OK\r\n");

		new Redis($connection, ['database' => 0]);
	}

	/**
	 *
	 */
	public function testNonZeroDatabase(): void
	{
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once()->with("*2\r\n$6\r\nSELECT\r\n$1\r\n1\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("+OK\r\n");

		new Redis($connection, ['database' => 1]);
	}

	/**
	 *
	 */
	public function testMethodCall(): void
	{
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once()->with("*3\r\n$3\r\nSET\r\n$1\r\nx\r\n$1\r\n0\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("+OK\r\n");

		$redis = new Redis($connection);

		$redis->set('x', 0);
	}

	/**
	 *
	 */
	public function testMultiWordMethodCall(): void
	{
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once()->with("*2\r\n$6\r\nCONFIG\r\n$7\r\nREWRITE\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("+OK\r\n");

		$redis = new Redis($connection);

		$redis->configRewrite();
	}

	/**
	 *
	 */
	public function testDashSeparatedCommand(): void
	{
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once()->with("*2\r\n$7\r\nCLUSTER\r\n$16\r\nSET-CONFIG-EPOCH\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("+OK\r\n");

		$redis = new Redis($connection);

		$redis->clusterSetConfigEpoch();
	}

	/**
	 *
	 */
	public function testException(): void
	{
		$this->expectException(RedisException::class);

		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("-ERR unknown command 'foobar'\r\n");

		$redis = new Redis($connection);

		$redis->foobar();
	}

	/**
	 *
	 */
	public function testStatusReply(): void
	{
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("+OK\r\n");

		$redis = new Redis($connection);

		$this->assertSame('OK', $redis->foobar());
	}

	/**
	 *
	 */
	public function testIntegerReply(): void
	{
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn(":1\r\n");

		$redis = new Redis($connection);

		$this->assertSame(1, $redis->foobar());
	}

	/**
	 *
	 */
	public function testBulkReply(): void
	{
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("$6\r\n");

		$connection->shouldReceive('read')->once()->andReturn("foobar\r\n");

		$redis = new Redis($connection);

		$this->assertSame('foobar', $redis->foobar());
	}

	/**
	 *
	 */
	public function testBulkNullReply(): void
	{
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("$-1\r\n");

		$redis = new Redis($connection);

		$this->assertSame(null, $redis->foobar());
	}

	/**
	 *
	 */
	public function testMultiBulkReply(): void
	{
		$connection = Mockery::mock(Connection::class);

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
	public function testMultiBulkMixedReply(): void
	{
		$connection = Mockery::mock(Connection::class);

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
	public function testMultiBulkEmptyReply(): void
	{
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("*0\r\n");

		$redis = new Redis($connection);

		$this->assertSame([], $redis->foobar());
	}

	/**
	 *
	 */
	public function testMultiBulkNullReply(): void
	{
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("*-1\r\n");

		$redis = new Redis($connection);

		$this->assertSame(null, $redis->foobar());
	}

	/**
	 *
	 */
	public function testInvalidResponse(): void
	{
		$this->expectException(RedisException::class);

		$this->expectExceptionMessage('Unable to handle server response [ foobar ].');

		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn('foobar');

		$redis = new Redis($connection);

		$redis->foobar();
	}

	/**
	 *
	 */
	public function testSubscribeTo(): void
	{
		$connection = Mockery::mock(Connection::class);

		$redis = new Redis($connection);

		$connection->shouldReceive('write')->once()->with("*2\r\n$9\r\nSUBSCRIBE\r\n$3\r\nfoo\r\n");

		//

		$connection->shouldReceive('readLine')->once()->andReturn("*3\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("$9\r\n");

		$connection->shouldReceive('read')->once()->andReturn("subscribe\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("$3\r\n");

		$connection->shouldReceive('read')->once()->andReturn("foo\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn(":1\r\n");

		//

		$connection->shouldReceive('write')->once()->with("*2\r\n$11\r\nUNSUBSCRIBE\r\n$3\r\nfoo\r\n");

		//

		$connection->shouldReceive('readLine')->once()->andReturn("*3\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("$11\r\n");

		$connection->shouldReceive('read')->once()->andReturn("unsubscribe\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("$3\r\n");

		$connection->shouldReceive('read')->once()->andReturn("foo\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn(":0\r\n");

		//

		$redis->subscribeTo(['foo'], function($message)
		{
			$this->assertSame('subscribe', $message->getType());

			return false;
		}, ['message', 'subscribe']);
	}

	/**
	 *
	 */
	public function testSubscribeToPattern(): void
	{
		$connection = Mockery::mock(Connection::class);

		$redis = new Redis($connection);

		$connection->shouldReceive('write')->once()->with("*2\r\n$10\r\nPSUBSCRIBE\r\n$3\r\nf?o\r\n");

		//

		$connection->shouldReceive('readLine')->once()->andReturn("*3\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("$10\r\n");

		$connection->shouldReceive('read')->once()->andReturn("psubscribe\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("$3\r\n");

		$connection->shouldReceive('read')->once()->andReturn("foo\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn(":1\r\n");

		//

		$connection->shouldReceive('write')->once()->with("*2\r\n$12\r\nPUNSUBSCRIBE\r\n$3\r\nf?o\r\n");

		//

		$connection->shouldReceive('readLine')->once()->andReturn("*3\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("$12\r\n");

		$connection->shouldReceive('read')->once()->andReturn("punsubscribe\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("$3\r\n");

		$connection->shouldReceive('read')->once()->andReturn("foo\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn(":0\r\n");

		//

		$redis->subscribeToPattern(['f?o'], function($message)
		{
			$this->assertSame('psubscribe', $message->getType());

			return false;
		}, ['pmessage', 'psubscribe']);
	}
}
