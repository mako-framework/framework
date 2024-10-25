<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\redis;

use mako\redis\Connection;
use mako\redis\exceptions\RedisException;
use mako\redis\Redis;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class RedisTest extends TestCase
{
	protected const int CRLF_LENGTH = 2;

	/**
	 *
	 */
	public function testResp3(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once()->with("*2\r\n$5\r\nHELLO\r\n$1\r\n3\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("+OK\r\n");

		new Redis($connection, ['resp' => 3]);
	}

	/**
	 *
	 */
	public function testAuth(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once()->with("*2\r\n$4\r\nAUTH\r\n$6\r\nfoobar\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("+OK\r\n");

		new Redis($connection, ['password' => 'foobar']);
	}

	/**
	 *
	 */
	public function testAuthWithUsername(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once()->with("*3\r\n$4\r\nAUTH\r\n$6\r\nfoobar\r\n$6\r\nfoobar\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("+OK\r\n");

		new Redis($connection, ['username' => 'foobar', 'password' => 'foobar']);
	}

	/**
	 *
	 */
	public function testZeroDatabase(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
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
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
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
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
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
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
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
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once()->with("*2\r\n$7\r\nCLUSTER\r\n$16\r\nSET-CONFIG-EPOCH\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("+OK\r\n");

		$redis = new Redis($connection);

		$redis->clusterSetConfigEpoch();
	}

	/**
	 *
	 */
	public function testSimpleStringResponse(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("+OK\r\n");

		$redis = new Redis($connection);

		$this->assertSame('OK', $redis->get()); // Not a valid call but we're just testing the response parsing
	}

	/**
	 *
	 */
	public function testBlobStringResponse(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("$6\r\n");

		$connection->shouldReceive('read')->once()->with(6 + static::CRLF_LENGTH)->andReturn("foobar\r\n");

		$redis = new Redis($connection);

		$this->assertSame('foobar', $redis->get()); // Not a valid call but we're just testing the response parsing
	}

	/**
	 *
	 */
	public function testNullBlobStringResponse(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("$-1\r\n");

		$redis = new Redis($connection);

		$this->assertSame(null, $redis->get()); // Not a valid call but we're just testing the response parsing
	}

	/**
	 *
	 */
	public function testStreamedBlobStringResponse(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("$?\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn(";4\r\n");

		$connection->shouldReceive('read')->once()->with(4 + static::CRLF_LENGTH)->andReturn("Hell\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn(";6\r\n");

		$connection->shouldReceive('read')->once()->with(6 + static::CRLF_LENGTH)->andReturn("o worl\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn(";1\r\n");

		$connection->shouldReceive('read')->once()->with(1 + static::CRLF_LENGTH)->andReturn("d\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn(";0\r\n");

		$redis = new Redis($connection);

		$this->assertSame('Hello world', $redis->get()); // Not a valid call but we're just testing the response parsing
	}

	/**
	 *
	 */
	public function testVerbatimStringResponse(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("=10\r\n");

		$connection->shouldReceive('read')->once()->with(10 + static::CRLF_LENGTH)->andReturn("txt:foobar\r\n");

		$redis = new Redis($connection);

		$this->assertSame('foobar', $redis->get()); // Not a valid call but we're just testing the response parsing
	}

	/**
	 *
	 */
	public function testNumberResponse(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn(":1\r\n");

		$redis = new Redis($connection);

		$this->assertSame(1, $redis->get()); // Not a valid call but we're just testing the response parsing
	}

	/**
	 *
	 */
	public function testDoubleResponse(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn(",1.1\r\n");

		$redis = new Redis($connection);

		$this->assertSame(1.1, $redis->get()); // Not a valid call but we're just testing the response parsing
	}

	/**
	 *
	 */
	public function testDoubleResponseWithInfinity(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn(",inf\r\n");

		$redis = new Redis($connection);

		$this->assertSame(INF, $redis->get()); // Not a valid call but we're just testing the response parsing
	}

	/**
	 *
	 */
	public function testDoubleResponseWithNegativeInfinity(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn(",-inf\r\n");

		$redis = new Redis($connection);

		$this->assertSame(-INF, $redis->get()); // Not a valid call but we're just testing the response parsing
	}

	/**
	 *
	 */
	public function testBigNumberResponse(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("(3492890328409238509324850943850943825024385\r\n");

		$redis = new Redis($connection);

		$this->assertSame('3492890328409238509324850943850943825024385', $redis->get()); // Not a valid call but we're just testing the response parsing
	}

	/**
	 *
	 */
	public function testBooleanResponseWithTrue(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("#t\r\n");

		$redis = new Redis($connection);

		$this->assertSame(true, $redis->get()); // Not a valid call but we're just testing the response parsing
	}

	/**
	 *
	 */
	public function testBooleanResponseWithFalse(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("#f\r\n");

		$redis = new Redis($connection);

		$this->assertSame(false, $redis->get()); // Not a valid call but we're just testing the response parsing
	}

	/**
	 *
	 */
	public function testArrayResponse(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("*2\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("$3\r\n");

		$connection->shouldReceive('read')->once()->andReturn("foo\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("$3\r\n");

		$connection->shouldReceive('read')->once()->andReturn("bar\r\n");

		$redis = new Redis($connection);

		$this->assertSame(['foo', 'bar'], $redis->get()); // Not a valid call but we're just testing the response parsing
	}

	/**
	 *
	 */
	public function testArrayResponseWithMixedTypes(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("*2\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn(":3\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("$3\r\n");

		$connection->shouldReceive('read')->once()->andReturn("bar\r\n");

		$redis = new Redis($connection);

		$this->assertSame([3, 'bar'], $redis->get()); // Not a valid call but we're just testing the response parsing
	}

	/**
	 *
	 */
	public function testEmptyArrayResponse(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("*0\r\n");

		$redis = new Redis($connection);

		$this->assertSame([], $redis->get()); // Not a valid call but we're just testing the response parsing
	}

	/**
	 *
	 */
	public function testNullArrayResponse(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("*-1\r\n");

		$redis = new Redis($connection);

		$this->assertSame(null, $redis->get()); // Not a valid call but we're just testing the response parsing
	}

	/**
	 *
	 */
	public function testStreamedArrayResponse(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("*?\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn(":1\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn(":2\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn(".\r\n");

		$redis = new Redis($connection);

		$this->assertSame([1, 2], $redis->get()); // Not a valid call but we're just testing the response parsing
	}

	/**
	 *
	 */
	public function testMapResponse(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("%2\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("+first\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn(":1\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("+second\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn(":2\r\n");

		$redis = new Redis($connection);

		$this->assertSame(['first' => 1, 'second' => 2], $redis->get()); // Not a valid call but we're just testing the response parsing
	}

	/**
	 *
	 */
	public function testStreamedMapResponse(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("%?\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("+first\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn(":1\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("+second\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn(":2\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn(".\r\n");

		$redis = new Redis($connection);

		$this->assertSame(['first' => 1, 'second' => 2], $redis->get()); // Not a valid call but we're just testing the response parsing
	}

	/**
	 *
	 */
	public function testSetResponse(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("~3\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("$3\r\n");

		$connection->shouldReceive('read')->once()->andReturn("foo\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("$3\r\n");

		$connection->shouldReceive('read')->once()->andReturn("bar\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("$3\r\n");

		$connection->shouldReceive('read')->once()->andReturn("bar\r\n");

		$redis = new Redis($connection);

		$this->assertSame(['foo', 'bar'], $redis->get()); // Not a valid call but we're just testing the response parsing
	}

	/**
	 *
	 */
	public function testStreamedSetResponse(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("~?\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn(":1\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn(":2\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn(":2\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn(".\r\n");

		$redis = new Redis($connection);

		$this->assertSame([1, 2], $redis->get()); // Not a valid call but we're just testing the response parsing
	}

	/**
	 *
	 */
	public function testAttributeResponse(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("|1\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("+key-popularity\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("%2\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("$1\r\n");

		$connection->shouldReceive('read')->once()->andReturn("a\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn(",0.1923\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("$1\r\n");

		$connection->shouldReceive('read')->once()->andReturn("b\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn(",0.0012\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("*2\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn(":2039123\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn(":9543892\r\n");

		$redis = new Redis($connection);

		$this->assertSame([2039123, 9543892], $redis->get()); // Not a valid call but we're just testing the response parsing

		$this->assertSame([['key-popularity' => ['a' => 0.1923, 'b' => 0.0012]]], $redis->getAttributes());
	}

	/**
	 *
	 */
	public function testPushResponse(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn(">2\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("$3\r\n");

		$connection->shouldReceive('read')->once()->andReturn("foo\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("$3\r\n");

		$connection->shouldReceive('read')->once()->andReturn("bar\r\n");

		$redis = new Redis($connection);

		$this->assertSame(['foo', 'bar'], $redis->get()); // Not a valid call but we're just testing the response parsing
	}

	/**
	 *
	 */
	public function testSimpleErrorResponse(): void
	{
		$this->expectException(RedisException::class);

		$this->expectExceptionMessage("ERR unknown command 'foobar'");

		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("-ERR unknown command 'foobar'\r\n");

		$redis = new Redis($connection);

		$redis->get(); // Not a valid call but we're just testing the response parsing
	}

	/**
	 *
	 */
	public function testBlobErrorResponse(): void
	{
		$this->expectException(RedisException::class);

		$this->expectExceptionMessage("ERR unknown command 'foobar'");

		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("!28\r\n");

		$connection->shouldReceive('read')->once()->andReturn("ERR unknown command 'foobar'\r\n");

		$redis = new Redis($connection);

		$redis->get(); // Not a valid call but we're just testing the response parsing
	}

	/**
	 *
	 */
	public function testNullResponse(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn("_\r\n");

		$redis = new Redis($connection);

		$this->assertSame(null, $redis->get()); // Not a valid call but we're just testing the response parsing
	}

	/**
	 *
	 */
	public function testInvalidResponse(): void
	{
		$this->expectException(RedisException::class);

		$this->expectExceptionMessage('Unable to handle server response [ foobar ].');

		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('write')->once();

		$connection->shouldReceive('readLine')->once()->andReturn('foobar');

		$redis = new Redis($connection);

		$redis->get(); // Not a valid call but we're just testing the response parsing
	}

	/**
	 *
	 */
	public function testSubscribeTo(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
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

		$redis->subscribeTo(['foo'], function ($message) {
			$this->assertSame('subscribe', $message->getType());

			return false;
		}, ['message', 'subscribe']);
	}

	/**
	 *
	 */
	public function testSubscribeToPattern(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
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

		$redis->subscribeToPattern(['f?o'], function ($message) {
			$this->assertSame('psubscribe', $message->getType());

			return false;
		}, ['pmessage', 'psubscribe']);
	}

	/**
	 *
	 */
	public function testMonitor(): void
	{
		/** @var \mako\redis\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$redis = new Redis($connection);

		$connection->shouldReceive('write')->once()->with("*1\r\n$7\r\nMONITOR\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("+OK\r\n");

		//

		$connection->shouldReceive('readLine')->once()->andReturn("$6\r\n");

		$connection->shouldReceive('read')->once()->andReturn("foobar\r\n");

		//

		$connection->shouldReceive('write')->once()->with("*1\r\n$4\r\nQUIT\r\n");

		$connection->shouldReceive('readLine')->once()->andReturn("+OK\r\n");

		//

		$redis->monitor(function ($line) {
			$this->assertSame('foobar', $line);

			return false;
		});
	}
}
