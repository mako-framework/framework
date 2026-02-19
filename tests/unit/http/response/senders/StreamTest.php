<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\response\senders;

use mako\http\Request;
use mako\http\Response;
use mako\http\response\Headers;
use mako\http\response\senders\Stream;
use mako\tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class StreamTest extends TestCase
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

		$headers->shouldReceive('add')->once()->with('X-Accel-Buffering', 'no');

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('sendHeaders')->once();

		(function () use ($headers): void {
			$this->headers = $headers;
		})->bindTo($response, Response::class)();

		return $response;
	}

	/**
	 *
	 */
	public function testBasicStream(): void
	{
		$eventStream = Mockery::mock(Stream::class, [function (Stream $stream): void {
			$stream->flush('hello, world!');
		}]);

		$eventStream->makePartial()->shouldAllowMockingProtectedMethods();

		$eventStream->shouldReceive('eraseAndDisableOutputBuffers')->once();

		$eventStream->shouldReceive('sendChunk')->once()->with('hello, world!');

		$eventStream->send($this->getRequest(), $this->getResponse());
	}

	/**
	 *
	 */
	public function testStreamWithMultipleChunks(): void
	{
		$eventStream = Mockery::mock(Stream::class, [function (Stream $stream): void {
			$stream->flush('hello, world 1!');
			$stream->flush('hello, world 2!');
		}]);

		$eventStream->makePartial()->shouldAllowMockingProtectedMethods();

		$eventStream->shouldReceive('eraseAndDisableOutputBuffers')->once();

		$eventStream->shouldReceive('sendChunk')->once()->with('hello, world 1!');
		$eventStream->shouldReceive('sendChunk')->once()->with('hello, world 2!');

		$eventStream->send($this->getRequest(), $this->getResponse());
	}

	/**
	 *
	 */
	public function testStreamWithContentTypeAndCharset(): void
	{
		$eventStream = Mockery::mock(Stream::class, [function (Stream $stream): void {
			$stream->flush('hello, world!');
		}, 'text/plain', 'UTF-8']);

		$eventStream->makePartial()->shouldAllowMockingProtectedMethods();

		$eventStream->shouldReceive('eraseAndDisableOutputBuffers')->once();

		$eventStream->shouldReceive('sendChunk')->once()->with('hello, world!');

		$response = $this->getResponse();

		$response->shouldReceive('setType')->once()->with('text/plain');
		$response->shouldReceive('setCharset')->once()->with('UTF-8');

		$eventStream->send($this->getRequest(), $response);
	}
}
