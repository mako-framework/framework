<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\response\senders;

use JsonSerializable;
use mako\http\Request;
use mako\http\Response;
use mako\http\response\Headers;
use mako\http\response\senders\EventStream;
use mako\http\response\senders\stream\event\Event;
use mako\http\response\senders\stream\event\Field;
use mako\http\response\senders\stream\event\Type;
use mako\tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Stringable;

#[Group('unit')]
class EventStreamTest extends TestCase
{
	/**
	 *
	 */
	protected function getRequest(): MockInterface&Request
	{
		return Mockery::mock(Request::class);
	}

	/**
	 *
	 */
	protected function getResponse(): MockInterface&Response
	{
		$headers = Mockery::mock(Headers::class);

		$headers->shouldReceive('add')->once()->with('Connection', 'keep-alive');
		$headers->shouldReceive('add')->once()->with('Cache-Control', 'no-cache');
		$headers->shouldReceive('add')->once()->with('X-Accel-Buffering', 'no');

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setType')->once()->with('text/event-stream', 'UTF-8');
		$response->shouldReceive('sendHeaders')->once();

		(function () use ($headers): void {
			$this->headers = $headers;
		})->bindTo($response, Response::class)();

		return $response;
	}

	/**
	 *
	 */
	public function testBasicEventStream(): void
	{
		$eventStream = Mockery::mock(EventStream::class, [function () {
			yield new Event(
				new Field(Type::DATA, 'hello, world!')
			);
		}]);

		$eventStream->makePartial()->shouldAllowMockingProtectedMethods();

		$eventStream->shouldReceive('eraseAndDisableOutputBuffers')->once();

		$eventStream->shouldReceive('sendChunk')->once()->with("data: hello, world!\n\n");

		$eventStream->send($this->getRequest(), $this->getResponse());
	}

	/**
	 *
	 */
	public function testEventStreamWithMultipleFields(): void
	{
		$eventStream = Mockery::mock(EventStream::class, [function () {
			yield new Event(
				new Field(Type::EVENT, 'greeting'),
				new Field(Type::DATA, 'hello, world!')
			);
		}]);

		$eventStream->makePartial()->shouldAllowMockingProtectedMethods();

		$eventStream->shouldReceive('eraseAndDisableOutputBuffers')->once();

		$eventStream->shouldReceive('sendChunk')->once()->with("event: greeting\ndata: hello, world!\n\n");

		$eventStream->send($this->getRequest(), $this->getResponse());
	}

	/**
	 *
	 */
	public function testEventStreamWithMultipleEvents(): void
	{
		$eventStream = Mockery::mock(EventStream::class, [function () {
			yield new Event(
				new Field(Type::EVENT, 'greeting'),
				new Field(Type::DATA, 'first hello')
			);
			yield new Event(
				new Field(Type::EVENT, 'greeting'),
				new Field(Type::DATA, 'second hello')
			);
		}]);

		$eventStream->makePartial()->shouldAllowMockingProtectedMethods();

		$eventStream->shouldReceive('eraseAndDisableOutputBuffers')->once();

		$eventStream->shouldReceive('sendChunk')->once()->with("event: greeting\ndata: first hello\n\n");
		$eventStream->shouldReceive('sendChunk')->once()->with("event: greeting\ndata: second hello\n\n");

		$eventStream->send($this->getRequest(), $this->getResponse());
	}

	/**
	 *
	 */
	public function testEventStreamWithStringable(): void
	{
		$eventStream = Mockery::mock(EventStream::class, [function () {
			yield new Event(
				new Field(Type::DATA, new class implements Stringable {
					public function __toString(): string
					{
						return 'this is a string';
					}
				})
			);
		}]);

		$eventStream->makePartial()->shouldAllowMockingProtectedMethods();

		$eventStream->shouldReceive('eraseAndDisableOutputBuffers')->once();

		$eventStream->shouldReceive('sendChunk')->once()->with("data: this is a string\n\n");

		$eventStream->send($this->getRequest(), $this->getResponse());
	}

	/**
	 *
	 */
	public function testEventStreamWithJsonSerializable(): void
	{
		$eventStream = Mockery::mock(EventStream::class, [function () {
			yield new Event(
				new Field(Type::DATA, new class implements JsonSerializable {
					public function jsonSerialize(): mixed
					{
						return [1, 2, 3];
					}
				})
			);
		}]);

		$eventStream->makePartial()->shouldAllowMockingProtectedMethods();

		$eventStream->shouldReceive('eraseAndDisableOutputBuffers')->once();

		$eventStream->shouldReceive('sendChunk')->once()->with("data: [1,2,3]\n\n");

		$eventStream->send($this->getRequest(), $this->getResponse());
	}
}
