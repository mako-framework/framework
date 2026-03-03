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
		$request = Mockery::mock(Request::class);

		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org');

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(Status::Found);

		(function () use ($responseHeaders): void {
			$this->headers = $responseHeaders;
		})->bindTo($response, Response::class)();

		$response->shouldReceive('sendHeaders')->once();

		//

		$redirect = new Redirect('http://example.org');

		$this->assertSame(Status::Found, $redirect->getStatus());

		$redirect->send($request, $response);
	}

	/**
	 *
	 */
	public function testSendWithConstructorStatus(): void
	{
		$request = Mockery::mock(Request::class);

		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org');

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(Status::Found);

		(function () use ($responseHeaders): void {
			$this->headers = $responseHeaders;
		})->bindTo($response, Response::class)();

		$response->shouldReceive('sendHeaders')->once();

		//

		$redirect = new Redirect('http://example.org', 302);

		$this->assertSame(Status::Found, $redirect->getStatus());

		$redirect->send($request, $response);
	}

	/**
	 *
	 */
	public function testSendWithStatus(): void
	{
		$request = Mockery::mock(Request::class);

		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org');

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(Status::Found);

		(function () use ($responseHeaders): void {
			$this->headers = $responseHeaders;
		})->bindTo($response, Response::class)();

		$response->shouldReceive('sendHeaders')->once();

		//

		$redirect = new Redirect('http://example.org');

		$redirect->setStatus(302);

		$this->assertSame(Status::Found, $redirect->getStatus());

		$redirect->send($request, $response);
	}

	/**
	 *
	 */
	public function testSendWithStatus301(): void
	{
		$request = Mockery::mock(Request::class);

		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org');

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(Status::MovedPermanently);

		(function () use ($responseHeaders): void {
			$this->headers = $responseHeaders;
		})->bindTo($response, Response::class)();

		$response->shouldReceive('sendHeaders')->once();

		//

		$redirect = new Redirect('http://example.org');

		$redirect->movedPermanently();

		$this->assertSame(Status::MovedPermanently, $redirect->getStatus());

		$redirect->send($request, $response);
	}

	/**
	 *
	 */
	public function testSendWithStatus302(): void
	{
		$request = Mockery::mock(Request::class);

		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org');

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(Status::Found);

		(function () use ($responseHeaders): void {
			$this->headers = $responseHeaders;
		})->bindTo($response, Response::class)();

		$response->shouldReceive('sendHeaders')->once();

		//

		$redirect = new Redirect('http://example.org');

		$redirect->found();

		$this->assertSame(Status::Found, $redirect->getStatus());

		$redirect->send($request, $response);
	}

	/**
	 *
	 */
	public function testSendWithStatus303(): void
	{
		$request = Mockery::mock(Request::class);

		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org');

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(Status::SeeOther);

		(function () use ($responseHeaders): void {
			$this->headers = $responseHeaders;
		})->bindTo($response, Response::class)();

		$response->shouldReceive('sendHeaders')->once();

		//

		$redirect = new Redirect('http://example.org');

		$redirect->seeOther();

		$this->assertSame(Status::SeeOther, $redirect->getStatus());

		$redirect->send($request, $response);
	}

	/**
	 *
	 */
	public function testSendWithStatus307(): void
	{
		$request = Mockery::mock(Request::class);

		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org');

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(Status::TemporaryRedirect);

		(function () use ($responseHeaders): void {
			$this->headers = $responseHeaders;
		})->bindTo($response, Response::class)();

		$response->shouldReceive('sendHeaders')->once();

		//

		$redirect = new Redirect('http://example.org');

		$redirect->temporaryRedirect();

		$this->assertSame(Status::TemporaryRedirect, $redirect->getStatus());

		$redirect->send($request, $response);
	}

	/**
	 *
	 */
	public function testSendWithStatus308(): void
	{
		$request = Mockery::mock(Request::class);

		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org');

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(Status::PermanentRedirect);

		(function () use ($responseHeaders): void {
			$this->headers = $responseHeaders;
		})->bindTo($response, Response::class)();

		$response->shouldReceive('sendHeaders')->once();

		//

		$redirect = new Redirect('http://example.org');

		$redirect->permanentRedirect();

		$this->assertSame(Status::PermanentRedirect, $redirect->getStatus());

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
