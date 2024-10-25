<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\response\builders;

use mako\http\exceptions\HttpException;
use mako\http\Request;
use mako\http\Response;
use mako\http\response\Headers;
use mako\http\response\senders\Redirect;
use mako\http\response\Status;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class RedirectTest extends TestCase
{
	/**
	 *
	 */
	public function testSend(): void
	{
		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		/** @var \mako\http\response\Headers|\Mockery\MockInterface $responseHeaders */
		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org');

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(Status::FOUND);

		(function () use ($responseHeaders): void {
			$this->headers = $responseHeaders;
		})->bindTo($response, Response::class)();

		$response->shouldReceive('sendHeaders')->once();

		//

		$redirect = new Redirect('http://example.org');

		$this->assertSame(Status::FOUND, $redirect->getStatus());

		$redirect->send($request, $response);
	}

	/**
	 *
	 */
	public function testSendWithConstructorStatus(): void
	{
		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		/** @var \mako\http\response\Headers|\Mockery\MockInterface $responseHeaders */
		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org');

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(Status::FOUND);

		(function () use ($responseHeaders): void {
			$this->headers = $responseHeaders;
		})->bindTo($response, Response::class)();

		$response->shouldReceive('sendHeaders')->once();

		//

		$redirect = new Redirect('http://example.org', 302);

		$this->assertSame(Status::FOUND, $redirect->getStatus());

		$redirect->send($request, $response);
	}

	/**
	 *
	 */
	public function testSendWithStatus(): void
	{
		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		/** @var \mako\http\response\Headers|\Mockery\MockInterface $responseHeaders */
		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org');

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(Status::FOUND);

		(function () use ($responseHeaders): void {
			$this->headers = $responseHeaders;
		})->bindTo($response, Response::class)();

		$response->shouldReceive('sendHeaders')->once();

		//

		$redirect = new Redirect('http://example.org');

		$redirect->setStatus(302);

		$this->assertSame(Status::FOUND, $redirect->getStatus());

		$redirect->send($request, $response);
	}

	/**
	 *
	 */
	public function testSendWithStatus301(): void
	{
		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		/** @var \mako\http\response\Headers|\Mockery\MockInterface $responseHeaders */
		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org');

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(Status::MOVED_PERMANENTLY);

		(function () use ($responseHeaders): void {
			$this->headers = $responseHeaders;
		})->bindTo($response, Response::class)();

		$response->shouldReceive('sendHeaders')->once();

		//

		$redirect = new Redirect('http://example.org');

		$redirect->movedPermanently();

		$this->assertSame(Status::MOVED_PERMANENTLY, $redirect->getStatus());

		$redirect->send($request, $response);
	}

	/**
	 *
	 */
	public function testSendWithStatus302(): void
	{
		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		/** @var \mako\http\response\Headers|\Mockery\MockInterface $responseHeaders */
		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org');

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(Status::FOUND);

		(function () use ($responseHeaders): void {
			$this->headers = $responseHeaders;
		})->bindTo($response, Response::class)();

		$response->shouldReceive('sendHeaders')->once();

		//

		$redirect = new Redirect('http://example.org');

		$redirect->found();

		$this->assertSame(Status::FOUND, $redirect->getStatus());

		$redirect->send($request, $response);
	}

	/**
	 *
	 */
	public function testSendWithStatus303(): void
	{
		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		/** @var \mako\http\response\Headers|\Mockery\MockInterface $responseHeaders */
		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org');

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(Status::SEE_OTHER);

		(function () use ($responseHeaders): void {
			$this->headers = $responseHeaders;
		})->bindTo($response, Response::class)();

		$response->shouldReceive('sendHeaders')->once();

		//

		$redirect = new Redirect('http://example.org');

		$redirect->seeOther();

		$this->assertSame(Status::SEE_OTHER, $redirect->getStatus());

		$redirect->send($request, $response);
	}

	/**
	 *
	 */
	public function testSendWithStatus307(): void
	{
		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		/** @var \mako\http\response\Headers|\Mockery\MockInterface $responseHeaders */
		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org');

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(Status::TEMPORARY_REDIRECT);

		(function () use ($responseHeaders): void {
			$this->headers = $responseHeaders;
		})->bindTo($response, Response::class)();

		$response->shouldReceive('sendHeaders')->once();

		//

		$redirect = new Redirect('http://example.org');

		$redirect->temporaryRedirect();

		$this->assertSame(Status::TEMPORARY_REDIRECT, $redirect->getStatus());

		$redirect->send($request, $response);
	}

	/**
	 *
	 */
	public function testSendWithStatus308(): void
	{
		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		/** @var \mako\http\response\Headers|\Mockery\MockInterface $responseHeaders */
		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org');

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(Status::PERMANENT_REDIRECT);

		(function () use ($responseHeaders): void {
			$this->headers = $responseHeaders;
		})->bindTo($response, Response::class)();

		$response->shouldReceive('sendHeaders')->once();

		//

		$redirect = new Redirect('http://example.org');

		$redirect->permanentRedirect();

		$this->assertSame(Status::PERMANENT_REDIRECT, $redirect->getStatus());

		$redirect->send($request, $response);
	}

	/**
	 *
	 */
	public function testInvalidStatusCode(): void
	{
		$this->expectException(HttpException::class);

		$this->expectExceptionMessage('Unsupported redirect status code [ 404 ].');

		new Redirect('http://example.org', 404);
	}
}
