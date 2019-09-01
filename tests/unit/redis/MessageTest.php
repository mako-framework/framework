<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\redis;

use mako\redis\Message;
use mako\redis\RedisException;
use mako\tests\TestCase;

/**
 * @group unit
 */
class MessageTest extends TestCase
{
	/**
	 *
	 */
	public function testMessage(): void
	{
		$message = new Message(['message', 'foo', 'bar']);

		$this->assertSame('message', $message->getType());
		$this->assertSame('foo', $message->getChannel());
		$this->assertSame('bar', $message->getBody());
		$this->assertSame('bar', (string) $message);
		$this->assertNull($message->getPattern());
	}

	/**
	 *
	 */
	public function testPmessage(): void
	{
		$message = new Message(['pmessage', 'f?o', 'foo', 'bar']);

		$this->assertSame('pmessage', $message->getType());
		$this->assertSame('foo', $message->getChannel());
		$this->assertSame('bar', $message->getBody());
		$this->assertSame('bar', (string) $message);
		$this->assertSame('f?o', $message->getPattern());
	}

	/**
	 *
	 */
	public function testSubscribe(): void
	{
		$message = new Message(['subscribe', 'foo', 1]);

		$this->assertSame('subscribe', $message->getType());
		$this->assertSame('foo', $message->getChannel());
		$this->assertSame('1', $message->getBody());
		$this->assertSame('1', (string) $message);
		$this->assertNull($message->getPattern());
	}

	/**
	 *
	 */
	public function testUnsubscribe(): void
	{
		$message = new Message(['unsubscribe', 'foo', 0]);

		$this->assertSame('unsubscribe', $message->getType());
		$this->assertSame('foo', $message->getChannel());
		$this->assertSame('0', $message->getBody());
		$this->assertSame('0', (string) $message);
		$this->assertNull($message->getPattern());
	}

	/**
	 *
	 */
	public function testPsubscribe(): void
	{
		$message = new Message(['psubscribe', 'f?o', 1]);

		$this->assertSame('psubscribe', $message->getType());
		$this->assertNull($message->getChannel());
		$this->assertSame('1', $message->getBody());
		$this->assertSame('1', (string) $message);
		$this->assertSame('f?o', $message->getPattern());
	}

	/**
	 *
	 */
	public function testPunsubscribe(): void
	{
		$message = new Message(['psubscribe', 'f?o', 0]);

		$this->assertSame('psubscribe', $message->getType());
		$this->assertNull($message->getChannel());
		$this->assertSame('0', $message->getBody());
		$this->assertSame('0', (string) $message);
		$this->assertSame('f?o', $message->getPattern());
	}

	/**
	 *
	 */
	public function testPong(): void
	{
		$message = new Message(['pong', 'foo']);

		$this->assertSame('pong', $message->getType());
		$this->assertNull($message->getChannel());
		$this->assertNull($message->getBody());
		$this->assertSame('', (string) $message);
		$this->assertNull($message->getPattern());
	}

	/**
	 *
	 */
	public function testUnsupported(): void
	{
		$this->expectException(RedisException::class);

		$this->expectExceptionMessage('Unable to parse message of type [ foo ].');

		new Message(['foo']);
	}
}
